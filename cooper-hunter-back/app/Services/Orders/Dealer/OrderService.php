<?php

namespace App\Services\Orders\Dealer;

use App\Dto\Orders\Dealer\Onec\OrderCreateDto;
use App\Dto\Orders\Dealer\OrderDto;
use App\Dto\Orders\Dealer\OrderFilesDto;
use App\Dto\Orders\Dealer\OrderInvoiceOnecDto;
use App\Dto\Orders\Dealer\OrderItemDto;
use App\Dto\Orders\Dealer\OrderOnecDto;
use App\Enums\Formats\DatetimeEnum;
use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\Dealer\PaymentType;
use App\Events\Orders\Dealer\ApprovedOrderEvent;
use App\Imports\Order\Dealer\ProductImport;
use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Companies\Price;
use App\Models\Dealers\Dealer;
use App\Models\GlobalSettings\GlobalSetting;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Dealer\PackingSlipSerialNumber;
use App\Models\Orders\Dealer\PrimaryItem;
use App\Notifications\Orders\Dealer\SendOrderToCommercialManagerNotification;
use App\Notifications\Orders\Dealer\SendOrderToManagerNotification;
use App\Repositories\Catalog\Product\ProductRepository;
use App\Repositories\Companies\CompanyPriceRepository;
use App\Repositories\Companies\CompanyRepository;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Excel\Excel;
use App\Services\OneC\RequestService;
use App\Traits\SimpleHasher;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Carbon\CarbonImmutable;
use Core\Exceptions\TranslatedException;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File as FileModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Notification;

class OrderService
{
    protected const ORDER_FILES_FOLDER = 'dealer-order';

    public function __construct(protected OrderItemService $itemService)
    {}

    public function copy(Order $model, Dealer $dealer): Order
    {
        $copy = new Order();
        $copy->dealer_id = $dealer->id;
        $copy->payment_type = $model->payment_type;
        $copy->delivery_type = $model->delivery_type;
        $copy->shipping_address_id = $model->shipping_address_id;
        $copy->comment = $model->comment;
        $copy->payment_card_id = $model->payment_card_id;

        $copy = $this->setStatus($copy, OrderStatus::DRAFT());

        $copy->save();

        $this->itemService->copies($copy, $model->items);

        return $copy;
    }

    public function setStatus(
        Order $model,
        OrderStatus $status,
        bool $save = false,
        string|null $hash = null
    ): Order
    {
        $model->status = $status;
        if ($status->isApproved()) {
            if(!$model->approved_at){
                $model->approved_at = CarbonImmutable::now();
            }

            if(!$model->equalsHash($hash)){
                event(new ApprovedOrderEvent($model, $this->isChangeOrder($model)));
                $model->update(['hash' => $hash]);
            }
        }

        if ($save) {
            $model->save();
        }

        return $model;
    }

    private function isChangeOrder(Order $model): bool
    {
        $was = [];
        $became = [];
        foreach ($model->items()->with('primary')->withTrashed()->get() as $k => $item){
            $was[$k]['qty'] = $item?->primary?->qty;
            $was[$k]['price'] = $item?->primary?->price;
            $was[$k]['del'] = null;
            $became[$k]['qty'] = $item->qty;
            $became[$k]['price'] = $item->price;
            $became[$k]['del'] = $item->deleted_at;
        }

        return ($model->hash == null ? false : true) || (SimpleHasher::hash($was) !== SimpleHasher::hash($became));
    }

    public function sendToOnec(Order $model): Order
    {
        /** @var $reqService RequestService */
        $reqService = app(RequestService::class);

        if ($model->guid) {
            $reqService->updateDealerOrder($model);
        } else {
            logger_info("REQUEST CREATE ORDER");
            $reqService->createDealerOrder($model);
        }

        return $model->refresh();
    }

    public function createOnec(OrderCreateDto $dto): Order
    {
        /** @var $companyRepo CompanyRepository */
        $companyRepo = resolve(CompanyRepository::class);
        /** @var $company Company */
        $company = $companyRepo->getBy('guid', $dto->companyGuid);

        /** @var $dealer Dealer */
        $dealer = $company->mainDealer;
        if(!$dealer){
            throw new \Exception("Сan't find the main dealer in the company [{$dto->companyGuid}]");
        }

        /** @var $repo OrderRepository */
        $repo = resolve(OrderRepository::class);

        if($repo->checkUniqPO($dealer, $dto->po)){
            throw new \Exception("PO [$dto->po] is not unique");
        }

        if(!key_exists(
            $dto->shippingAddressID,
            $dealer->company->shippingAddresses->pluck('id', 'id')->toArray()
        )){
            throw new \Exception("This company [{$company->business_name}] does not have this shipping address");
        }

        $model = new Order();
        $model->guid = $dto->guid;
        $model->dealer_id = $dealer->id;
        $model->po = $dto->po;
        $model->delivery_type = $dto->deliveryType;
        $model->payment_type = $dto->paymentType;
        $model->type = $dto->type;
        $model->comment = $dto->comment;
        $model->tax = $dto->tax;
        $model->total = $dto->total;
        $model->total_discount = $dto->totalDiscount;
        $model->total_with_discount = $dto->totalWithDiscount;
        $model->shipping_price = $dto->shippingPrice;
        $model->shipping_address_id = $dto->shippingAddressID;
        $model->terms = $dto->term;

        $model = $this->setStatus($model, OrderStatus::SENT());

        $model->save();

        // todo - когда прийдет инфа от заказчика, оптимизировать, получать коллекции товаров и цен, и работать с коллекциями
        /** @var $priceRepo CompanyPriceRepository */
        $priceRepo = resolve(CompanyPriceRepository::class);
        /** @var $productRepo ProductRepository */
        $productRepo = resolve(ProductRepository::class);
        foreach ($dto->items as $item){
            /** @var $item OrderItemDto */

            /** @var $product Product */
            $product = $productRepo->getBy(
                'guid',
                $item->guid,
                withException: true,
                exceptionMessage: "Product not found by guide [{$item->guid}]"
            );

            /** @var $price Price */
            $price = $priceRepo->getByFields([
                'company_id' => $dealer->company_id,
                'product_id' => $product->id,
            ],
                withException: true,
                exceptionMessage: "Price not found by company [{$dealer->company->business_name}] fo this product [$item->guid]"
            );

            $this->itemService->create($model, $item, $product);
        }

        $model->refresh();

        $this->createPrimaryItems($model);

        $model = $this->setStatus($model,OrderStatus::APPROVED(), true, $dto->hash);

        return $model;
    }

    public function updateOnec(Order $model, OrderOnecDto $dto): Order
    {
        if ($dto->tax) {
            $model->tax = $dto->tax;
        }
        if ($dto->shippingPrice) {
            $model->shipping_price = $dto->shippingPrice;
        }
        if ($dto->total) {
            $model->total = $dto->total;
        }
        if ($dto->totalDiscount) {
            $model->total_discount = $dto->totalDiscount;
        }
        if ($dto->totalWithDiscount) {
            $model->total_with_discount = $dto->totalWithDiscount;
        }

        if ($dto->term) {
            // todo deprecated
//            $term = null;
//            $terms = $model->dealer->company->terms;
//            foreach ($terms ?? [] as $item) {
//                if (data_get($item, 'guid') == $dto->term) {
//                    $term = $item;
//                    break;
//                }
//            }
//            if ($term == null) {
//                throw new Exception('No matching term found');
//            }
            $model->terms = $dto->term;
        }
        if (!empty($dto->items)) {
            $guids = $model->items()->with('product')->get()->pluck(
                'product.guid',
                'product.guid'
            );

            foreach ($dto->items as $item) {
                /** @var $item OrderItemDto */
                /** @var $orderItem Item */
                $orderItem = $model
                    ->items()
                    ->whereHas('product', fn($b) => $b->where('guid', $item->guid))
                    ->first();

                if (!$orderItem) {
                    throw new Exception('There is no item in the order by guid - ' . $item->guid);
                }

                $orderItem->discount = $item->discount;
                $orderItem->discount_total = $item->discount_total;
                $orderItem->qty = $item->qty;
                $orderItem->total = $item->total;
                $orderItem->price = $item->price;
                $orderItem->description = $item->description;
                $orderItem->save();

                $guids->forget($item->guid);
            }

            if ($guids->isNotEmpty()) {
                foreach ($guids as $guid) {
                    $model
                        ->items()
                        ->whereHas('product', fn($b) => $b->where('guid', $guid))
                        ->delete();
                }
            }
        }

        $model->save();

        if($model->packingSlips->isNotEmpty()){
            /** @var $packingSlipService PackingSlipService */
            $packingSlipService = resolve(PackingSlipService::class);
            foreach ($model->packingSlips as $slip){
                /** @var $slip PackingSlip */
                if($slip->invoice && $slip->invoice_at){
                    $packingSlipService->generateAndSavePackingSlipInvoice($slip);
                }
            }
        }

        if ($dto->status) {
            $model = $this->setStatus($model, $dto->status, hash: $dto->hash, save: true);
        }

        return $model;
    }

    public function addInvoiceData(Order $model, OrderInvoiceOnecDto $dto): Order
    {
        $model->invoice = $dto->invoice;
        $model->invoice_at = $dto->invoiceAt;

        if ($model->invoice && $model->invoice_at) {
            $model->has_invoice = true;
        }

        $model->tax = $dto->tax;
        $model->shipping_price = $dto->shippingPrice;
        $model->total = $dto->total;
        $model->total_discount = $dto->totalDiscount;
        $model->total_with_discount = $dto->totalWithDiscount;

        $model->save();

        return $model;
    }

    public function delete(Order $model): bool
    {
        return $model->delete();
    }

    public function uploadFilesFromOnec(
        Order $model,
        OrderFilesDto $files
    ): Order
    {
        $this->deleteOldFiles(Storage::disk('s3'), $model);

        $pathStorage = Storage::disk('public')
            ->getDriver()
            ->getAdapter()
            ->getPathPrefix();

        if (!file_exists("{$pathStorage}temp")) {
            mkdir("{$pathStorage}temp", 0777, true);
        }

        foreach ($files->getFiles() as $file) {
            $basename = $file->getName() . '.' . $file->getExtension();
            $filename = "{$pathStorage}temp/$basename";

            file_put_contents($filename, $file->getDecodedFileData());

            $filePath = self::ORDER_FILES_FOLDER . "/{$model->id}";

            $path = Storage::disk('s3')
                ->putFile($filePath, new FileModel($filename));

            $url = Storage::disk('s3')->url($path);

            $newFile = [
                'name' => $basename,
                'url' => $url
            ];

            if ($model->files) {
                $tmp = $model->files;
                $tmp[] = $newFile;

                $model->files = $tmp;
            } else {
                $model->files = [$newFile];
            }
        }

        $model->save();

        Storage::deleteDirectory('temp');

        return $model;
    }

    private function deleteOldFiles(Filesystem $disk, Order $order): void
    {
        $files = [];

        foreach ($order->files ?? [] as ['name' => $name, 'url' => $url]) {
            $url = $this->prepareFileUrlToDelete($url);

            info('file to delete: ' . $url);
            $files[] = $url;
        }

        $order->files = [];

        if (!empty($files)) {
            $deleted = $disk->delete($files);
            info('is deleted: ' . json_encode($deleted));
        }
    }

    public function prepareFileUrlToDelete(string $url): string
    {
        return mb_substr($url, mb_strpos($url, self::ORDER_FILES_FOLDER), mb_strlen($url));
    }

    public function createFromFile(Dealer $dealer, UploadedFile $file): Order
    {
        // получаем коллекцию данных (id - товара и кол-во) из файла
        $import = new ProductImport();
        Excel::import($import, $file);
        /** @var $data Collection */
        $data = $import->data;

        if ($data->isEmpty()) {
            throw new TranslatedException(
                __('messages.dealer.order.file.not items for create'), 502
            );
        }

        // по id товаров получаем цена на эти товары для данной компании
        $productIds = $data->pluck('id')->toArray();
        $companyProductPrice =
            $dealer->company->prices->whereIn('product_id', $productIds);

        if ($companyProductPrice->isEmpty()) {
            throw new TranslatedException(
                __('messages.dealer.order.file.not items for create'), 502
            );
        }

        // создаем пустой заказ
        $order = $this->create($dealer);

        // привязываем товары к заказу
        foreach ($companyProductPrice as $price) {
            /** @var $price Price */
            $this->itemService->add(
                $order,
                $price,
                $data->where('id', $price->product_id)->first()['qty']
            );
        }

        return $order->refresh();
    }

    public function create(Dealer $dealer): Order
    {
        $model = new Order();
        $model = $this->setStatus($model, OrderStatus::DRAFT());
        $model->delivery_type = DeliveryType::NONE();
        $model->payment_type = PaymentType::NONE();
        $model->dealer_id = $dealer->id;

        $model->save();

        return $model->refresh();
    }

    public function isChangeProductPrice(Order $order, Dealer $dealer): bool
    {
        if ($order->items->isEmpty() || !$order->status->isDraft()) {
            return false;
        }

        $orderProductPrice =
            $order->items->pluck('price', 'product_id')->toArray();

        $company = $dealer->company;
        $companyProductPrice = $company->prices->whereIn(
            'product_id',
            array_keys($orderProductPrice)
        )
            ->pluck('price', 'product_id')->toArray();

        $change = false;
        foreach ($companyProductPrice as $productId => $price) {
            if ($orderProductPrice[$productId] !== $price) {
                $change = true;
                continue;
            }
        }

        return $change;
    }

    public function changeProductPrice(Order $order, Dealer $dealer): Order
    {
        if ($order->items->isEmpty()) {
            return $order;
        }

        $orderProductPrice =
            $order->items->pluck('price', 'product_id')->toArray();

        $company = $dealer->company;
        $companyProductPrice = $company->prices->whereIn(
            'product_id',
            array_keys($orderProductPrice)
        )
            ->pluck('price', 'product_id')->toArray();

        foreach ($order->items as $item) {
            /** @var $item Item */
            $item->update(['price' => $companyProductPrice[$item->product_id]]);
        }

        return $order->refresh();
    }

    public function update(Order $model, OrderDto $dto): Order
    {
        if ($dto->type) {
            $model->type = $dto->type;
        }
        if ($dto->po) {
            $model->po = $dto->po;
        }
        if ($dto->comment) {
            $model->comment = $dto->comment;
        }
        if ($dto->shippingAddressID) {
            $model->shipping_address_id = $dto->shippingAddressID;
        }
        if (!$dto->paymentType->isNone()) {
            $model->payment_type = $dto->paymentType;
            if ($dto->paymentType->isCard()) {
                $model->payment_card_id = $dto->paymentCardID;
            } else {
                $model->payment_card_id = null;
            }
        }

        if (!$dto->deliveryType->isNone()) {
            $model->delivery_type = $dto->deliveryType;
        }

        $model->save();

        return $model;
    }

    public function generateAndSavePdf(Order $model): string
    {
        $settings = GlobalSetting::first();

        if($model->invoice && !($model->has_invoice)){
            throw new \Exception(__('exceptions.dealer.order.not has invoice', ['guid' => $model->guid]));
        }

        $data = collect([
            'model' => $model->load(['items.product', 'dealer.company']),
            'setting' => $settings
        ]);

        PdfFacade::setPaper('A4')
            ->setOptions(['isRemoteEnabled' => true])
            ->loadView(
                view: 'pdf.order-estimate',
                data: [
                    'language' => App::getLocale(),
                    'name' => $model->invoice && $model->has_invoice ? 'invoice' : 'estimate',
                    'pdf_data' => $data
                ],
                encoding: 'UTF-8'
            )
            ->save(
                $model->invoice && $model->has_invoice
                    ? $model->getInvoiceStoragePath()
                    : $model->getEstimateStoragePath()
            );

        return $model->invoice && $model->has_invoice
            ? $model->getInvoiceStorageUrl()
            : $model->getEstimateStorageUrl()
            ;
    }

    public function generateExcelSerialNumber(Order $model): string
    {
        $basePath = storage_path('app/public/exports/dealer-order/');

        File::ensureDirectoryExists($basePath);
        $fileName = "serial-number-{$model->id}.xlsx";
        $file = $basePath . $fileName;

        $data = [];

        foreach ($model->packingSlips as $packingSlip){
            /** @var $packingSlip PackingSlip */
            foreach ($packingSlip->serialNumbers as $itemSlip){
                /** @var $itemSlip PackingSlipSerialNumber */
                $data[] = [
                    __('messages.file.id') => $itemSlip->id,
                    __('messages.file.serial_number') => $itemSlip->serial_number,
                    __('messages.file.name') => $itemSlip->product->title,
                    __('messages.file.packing_slip') => $packingSlip->number,
                ];
            }
        }

        $sheets = new SheetCollection([
            'Units' => $data
        ]);

        (new FastExcel($sheets))->export($file);

        return url("/storage/exports/dealer-order/{$fileName}");
    }

    public function transformDataForReport(Collection $data): array
    {
        $companyIds =
            $data->pluck('dealer.company.business_name', 'dealer.company.id')
                ->toArray();

        $tmp = [];

        foreach ($companyIds as $id => $name) {
            $tmp[$id]['company_name'] = $name;
            $tmp[$id]['company_id'] = $id;

            if (!isset($tmp[$id]['total'])) {
                $tmp[$id]['total'] = 0;
            }

            foreach ($data as $model) {
                /** @var $model Order */
                if ($model->shippingAddress->company_id == $id) {
                    $tmp[$id]['locations'][$model->shipping_address_id]['location_name'] =
                        $model->shippingAddress->name;
                    $tmp[$id]['locations'][$model->shipping_address_id]['location_id'] =
                        $model->shipping_address_id;

                    if (!isset($tmp[$id]['locations'][$model->shipping_address_id]['total'])) {
                        $tmp[$id]['locations'][$model->shipping_address_id]['total'] =
                            0;
                    }
                    foreach ($model->items as $item) {
                        /** @var $item Item */
                        $tmp[$id]['locations'][$model->shipping_address_id]['items'][] =
                            [
                                'po' => $model->po,
                                'qty' => $item->qty,
                                'price' => $item->price,
                                'total' => $item->total,
                                'desc' => $item->description,
                                'product_title' => $item->product->title,
                                'date' => $model->approved_at?->format(
                                    DatetimeEnum::US_DATE_VIEW
                                )
                            ];
                        $tmp[$id]['locations'][$model->shipping_address_id]['total'] += $item->total;
                        $tmp[$id]['total'] += $item->total;
                    }
                }
            }
        }

        return $tmp;
    }

    public function generateExcelReport(array $data): string
    {
        $basePath = storage_path('app/public/exports/dealer-order/');

        $time = now()->timestamp;
        File::ensureDirectoryExists($basePath);
        $fileName = "report-" . CarbonImmutable::now()->timestamp . ".xlsx";
        $file = $basePath . $fileName;

        $tmp = [];
        $total = 0;
        foreach ($data ?? [] as $company) {
            $tmp[] = $this->fillDataForReportExcel($company);
            $total += (data_get($company, 'total', 0));
            foreach (data_get($company, 'locations', []) as $location) {
                $tmp[] = $this->fillDataForReportExcel($location);
                foreach (data_get($location, 'items', []) as $item) {
                    $tmp[] = $this->fillDataForReportExcel($item);
                }
            }
        }
        $tmp[] = $this->fillDataForReportExcel(['total' => $total]);

        $sheets = new SheetCollection([
            'Report' => $tmp
        ]);

        (new FastExcel($sheets))->export($file);

        return url("/storage/exports/dealer-order/{$fileName}");
    }

    private function fillDataForReportExcel(array $data): array
    {
        return [
            __('messages.file.report.company') => data_get(
                $data,
                'company_name'
            ),
            __('messages.file.report.location') => data_get(
                $data,
                'location_name'
            ),
            __('messages.file.report.date') => data_get($data, 'date'),
            __('messages.file.report.po') => data_get($data, 'po'),
            __('messages.file.report.product') => data_get(
                $data,
                'product_title'
            ),
            __('messages.file.report.description') => data_get($data, 'desc'),
            __('messages.file.report.qty') => data_get($data, 'qty'),
            __('messages.file.report.price') => data_get($data, 'price'),
            __('messages.file.report.amount') => data_get($data, 'total'),
        ];
    }

    public function sendEmailToManager(Order $model)
    {
        try {
            $email = $model->dealer?->company?->manager?->email->getValue();
            if($email){
                Notification::route('mail', $email)->notify(
                    new SendOrderToManagerNotification($model)
                );

                logger_info("SEND Email to a manager [{$email}]");
            }
        } catch (\Throwable $e) {
            throw new TranslatedException($e->getMessage(), 502);
        }
    }

    public function sendEmailToCommercialManager(Order $model)
    {
        try {
            $email = $model->dealer?->company?->commercialManager?->email->getValue();
            if($email){
                Notification::route('mail', $email)->notify(
                    new SendOrderToCommercialManagerNotification($model)
                );

                logger_info("SEND Email to a commercial manager [{$email}]");
            }
        } catch (\Throwable $e) {
            throw new TranslatedException($e->getMessage(), 502);
        }
    }

    public function createPrimaryItems(Order $order)
    {
        foreach ($order->items as $item){
            /** @var $item Item */
            $m = new PrimaryItem();
            $m->item_id = $item->id;
            $m->qty = $item->qty;
            $m->price = $item->price;
            $m->save();
        }
    }
}
