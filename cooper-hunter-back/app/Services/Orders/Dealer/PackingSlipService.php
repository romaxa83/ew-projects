<?php

namespace App\Services\Orders\Dealer;

use App\Dto\Orders\Dealer\OrderFilesDto;
use App\Dto\Orders\Dealer\OrderInvoiceOnecDto;
use App\Dto\Orders\Dealer\OrderPackingSlipDimensionsOnecDto;
use App\Dto\Orders\Dealer\OrderPackingSlipItemDto;
use App\Dto\Orders\Dealer\OrderPackingSlipOnecDto;
use App\Dto\Orders\Dealer\OrderPackingSlipsOnecDto;
use App\Dto\Orders\Dealer\OrderPackingSlipUpdateDto;
use App\Dto\Orders\Dealer\OrderSerialNumberOnecDto;
use App\Dto\Orders\Dealer\OrderSerialNumbersOnecDto;
use App\Models\GlobalSettings\GlobalSetting;
use App\Models\Orders\Dealer\Dimensions;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Dealer\PackingSlipItem;
use App\Models\Orders\Dealer\PackingSlipSerialNumber;
use App\Repositories\Catalog\Product\ProductRepository;
use App\Repositories\Orders\Dealer\PackingSlipRepository;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File as FileModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class PackingSlipService
{
    public function __construct(protected PackingSlipRepository $repo)
    {}

    public function addOrUpdatePackingSlips(
        Order $order,
        OrderPackingSlipsOnecDto $dto
    )
    {
        foreach ($dto->items as $item) {
            /** @var $item PackingSlipItem */
            if(
                $packingSlip = $this->repo->getBy('guid', $item->guid, ['dimensions', 'items'])
            ){
                /** @var $packingSlip PackingSlip */
                $this->updatePackingSlip($packingSlip, $item);
            } else {
                $this->addPackingSlip($order, $item);
            }
        }
    }

    public function addOrUpdatePackingSlipInvoice(
        PackingSlip $model,
        OrderInvoiceOnecDto $dto
    ): PackingSlip
    {
        $model = $this->fillInvoicePackingSlip($model, $dto);
        $model->save();

        if($model->invoice && $model->invoice_at && !($model->order->has_invoice)){
            $this->generateAndSavePackingSlipInvoice($model);
        }

        return $model;
    }

    public function addPackingSlip(
        Order $order,
        OrderPackingSlipOnecDto $dto
    ): PackingSlip
    {
        $model = new PackingSlip();
        $model->order_id = $order->id;
        $model->guid = $dto->guid;
        $model = $this->fillPackingSlip($model, $dto);
        $model->save();

        $this->addDims($model, $dto);

        foreach ($dto->items as $item) {
            /** @var OrderPackingSlipItemDto $item */
            $this->createOrUpdatePackingSlipItem($order, $model, $item);
        }

        return $model;
    }

    public function updatePackingSlip(
        PackingSlip $model,
        OrderPackingSlipOnecDto $dto
    ): PackingSlip
    {
        $model = $this->fillPackingSlip($model, $dto);
        $model->save();

        $this->addDims($model, $dto);

        foreach ($dto->items as $item) {
            /** @var OrderPackingSlipItemDto $item */
            $this->createOrUpdatePackingSlipItem($model->order, $model, $item);
        }

        return $model;
    }

    public function addDims(PackingSlip $model, OrderPackingSlipOnecDto $dto): void
    {
        if(!empty($dto->dimensions)){
            $model->dimensions()->delete();
            foreach ($dto->dimensions as $dims){
                /** @var OrderPackingSlipDimensionsOnecDto $dims */
                $this->createPackingSlipDimensions($model, $dims);
            }
        }
    }

    private function fillPackingSlip(
        PackingSlip $model,
        OrderPackingSlipOnecDto $dto
    ): PackingSlip
    {
        $model->number = $dto->number;
        $model->status = $dto->status;
        $model->tracking_number = $dto->trackingNumber;
        $model->tracking_company = $dto->trackingCompany;
        $model->shipped_at = $dto->shippedAt;

        return $model;
    }

    private function fillInvoicePackingSlip(
        PackingSlip $model,
        OrderInvoiceOnecDto $dto
    ): PackingSlip
    {
        $model->invoice = $dto->invoice;
        $model->invoice_at = $dto->invoiceAt;
        $model->tax = $dto->tax;
        $model->total = $dto->total;
        $model->total_discount = $dto->totalDiscount;
        $model->total_with_discount = $dto->totalWithDiscount;
        $model->shipping_price = $dto->shippingPrice;

        return $model;
    }

    public function createPackingSlipDimensions(
        PackingSlip $packingSlip,
        OrderPackingSlipDimensionsOnecDto $dto
    ): Dimensions
    {
        $model = new Dimensions();
        $model->packing_slip_id = $packingSlip->id;
        $model = $this->fillDimensions($model, $dto);
        $model->save();

        return $model;
    }

    private function fillDimensions(
        Dimensions $model,
        OrderPackingSlipDimensionsOnecDto $dto
    ): Dimensions
    {
        $model->pallet = $dto->pallet;
        $model->box_qty = $dto->boxQty;
        $model->type = $dto->type;
        $model->weight = $dto->weight;
        $model->width = $dto->width;
        $model->height = $dto->height;
        $model->depth = $dto->depth;
        $model->class_freight = $dto->classFreight;

        return $model;
    }

    public function createOrUpdatePackingSlipItem(
        Order $order,
        PackingSlip $packingSlip,
        OrderPackingSlipItemDto $dto
    ): PackingSlipItem
    {
        $orderItem = $order
            ->items()
            ->with('product')
            ->whereHas(
                'product',
                fn($b) => $b->where('guid', $dto->guid)
            )
            ->first();
        if (!$orderItem) {
            throw new \Exception("There is no product [guid - {$dto->guid}] in the order [guid - {$order->guid}]");
        }

        if(
            $model = $packingSlip->items()->with('product')
                ->whereHas('product', fn($b) => $b->where('guid', $dto->guid))
                ->first()
        ){
            /** @var $model PackingSlipItem */
            $model = $this->fillItem($model, $dto);
        } else {
            $model = new PackingSlipItem();
            $model->packing_slip_id = $packingSlip->id;
            $model->product_id = $orderItem->product->id;
            $model->order_item_id = $orderItem->id;
            $model = $this->fillItem($model, $dto);
        }

        $model->save();

        return $model;
    }

    private function fillItem(
        PackingSlipItem $model,
        OrderPackingSlipItemDto $dto
    ): PackingSlipItem
    {
        $model->qty = $dto->qty;
        $model->description = $dto->description;

        return $model;
    }

    public function updateDealer(
        PackingSlip $model,
        OrderPackingSlipUpdateDto $dto
    ): PackingSlip
    {
        $model->tracking_number = $dto->trackingNumber;
        $model->tracking_company = $dto->trackingCompany;

        $model->save();

        foreach ($dto->media as $image) {
            $model->addMedia($image)->toMediaCollection(
                $model->getMediaCollectionName()
            );
        }

        return $model;
    }

    public function addSerialNumbers(
        PackingSlip $model,
        OrderSerialNumbersOnecDto $dto
    ): array {
        $tmp = [];
        $model->serialNumbers()->delete();

        foreach ($dto->items as $item) {
            $res = $this->addSerialNumber($model, $item);
            if ($res) {
                $tmp[] = $res;
            }
        }

        return $tmp;
    }

    public function addSerialNumber(
        PackingSlip $model,
        OrderSerialNumberOnecDto $dto
    ): ?string
    {
        $res = null;
        $product = resolve(ProductRepository::class)
            ->getByFieldsObj(['guid' => $dto->guid], ['id']);

        if ($product) {
            if (
                $model->items->where('product_id', $product->id)->isNotEmpty()
            ) {
                $data = [];
                foreach ($dto->serialNumbers as $serialNumber) {
                    $data[] = [
                        'packing_slip_id' => $model->id,
                        'product_id' => $product->id,
                        'serial_number' => $serialNumber,
                    ];
                }

                PackingSlipSerialNumber::query()->upsert(
                    $data,
                    ['product_id', 'serial_number']
                );
            } else {
                $res = $dto->guid;
            }
        } else {
            $res = $dto->guid;
        }

        return $res;
    }

    public function generateAndSavePackingSlipInvoice(PackingSlip $packingSlip): void
    {
        $settings = GlobalSetting::first();

        $data = collect([
            'model' => $packingSlip->load([
                'items.product',
                'items.orderItem',
                'order.dealer.company'
            ]),
            'setting' => $settings
        ]);

        PdfFacade::setPaper('A4')
            ->setOptions(['isRemoteEnabled' => true])
            ->loadView(
                view: 'pdf.packing-slip-invoice',
                data: [
                    'language' => App::getLocale(),
                    'name' => 'packing-slip-invoice',
                    'pdf_data' => $data
                ],
                encoding: 'UTF-8'
            )
            ->save($packingSlip->getInvoiceFileStoragePath());
    }

    public function generateAndSavePackingSlipPdfFile(PackingSlip $packingSlip): string
    {
        $settings = GlobalSetting::first();

        $data = collect([
            'model' => $packingSlip->load(['items.product', 'order.dealer.company']),
            'setting' => $settings
        ]);

        PdfFacade::setPaper('A4')
            ->setOptions([
                'isRemoteEnabled' => true,
                'dpi' => 80,
            ])
            ->loadView(
                view: 'pdf.packing-slip-pdf',
                data: [
                    'language' => App::getLocale(),
                    'name' => 'packing-slip-pdf',
                    'pdf_data' => $data
                ],
                encoding: 'UTF-8'
            )
            ->save($packingSlip->getPdfFileStoragePath());

        return $packingSlip->getPdfFileStorageUrl();
    }

    public function generateExcel(PackingSlip $model): string
    {
        $basePath = storage_path('app/public/exports/dealer-order/');

        File::ensureDirectoryExists($basePath);
        $fileName = PackingSlip::EXCEL_FILE_PREFIX . "-{$model->id}.xlsx";
        $file = $basePath . $fileName;

        $data = [
            [null,'Comfortside LLC'],
            [null,'3550 NW 113th Court, Doral, FL 33178'],
            [null,'United States, Tel: 786-953-6706'],
            [],
            [null,null,null,null,'Packing slip#', null, $model->number],
            [],
            ['Release to', null, null,'Carrier:',null, null,'Date:'],
            ['Kingsway Marketing LLC.',null, null,'LTL', null, null,'12.07.2022'],
            ['Water Heaters only',null, null,'Tracking Number:', $model->tracking_number,null,'Time'],
            ['800 Mendelssohn Ave N, , Golden Valley',null,null, null,null, null,'12:34:57 PM'],
            ['Minnesota US 55427',null, null,'PO Number:',null, null,null],
            ['952-829-7200',null, null,$model->order->po,null, null,null],
            [],
            [],
            ['Product',null, 'Description',null, 'Qty'],
        ];


        $total = 0;
        foreach ($model->items as $item) {
            /** @var $item PackingSlipItem */
            $data[] = [
                'Product' => $item->product->title,
                null,
                'Description' => $item->description,
                null,
                'Qty' => $item->qty,
            ];
            $total += $item->qty;
        }

        $data[] = ['Total',null,null,null, $total];

        $sheets = new SheetCollection([
            'Units' => $data
        ]);

        (new FastExcel($sheets))->export($file);

        return url("/storage/exports/dealer-order/{$fileName}");
    }

    public function uploadFilesFromOnec(
        PackingSlip $model,
        OrderFilesDto $files
    ): PackingSlip
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

            $filePath = Order::ORDER_FILES_FOLDER . "/{$model->order->id}/" . PackingSlip::EXCEL_FILE_PREFIX . "/{$model->id}";

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

    private function deleteOldFiles(Filesystem $disk, PackingSlip $packingSlip): void
    {
        $files = [];

        foreach ($packingSlip->files ?? [] as ['name' => $name, 'url' => $url]) {
            $url = $this->prepareFileUrlToDelete($url);

            info('file to delete: ' . $url);
            $files[] = $url;
        }

        $packingSlip->files = [];

        if (!empty($files)) {
            $deleted = $disk->delete($files);
            info('is deleted: ' . json_encode($deleted));
        }
    }

    public function prepareFileUrlToDelete(string $url): string
    {
        return mb_substr($url, mb_strpos($url, Order::ORDER_FILES_FOLDER), mb_strlen($url));
    }
}
