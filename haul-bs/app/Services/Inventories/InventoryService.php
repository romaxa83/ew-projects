<?php

namespace App\Services\Inventories;

use App\Contracts\Orders\Orderable;
use App\Dto\Inventories\InventoryDto;
use App\Dto\Inventories\InventoryFeatureDto;
use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\OrderType;
use App\Events\Events\Inventories\Inventories\DeleteInventoryEvent;
use App\Exceptions\HasRelatedEntitiesException;
use App\Exports\Inventories\InventoryExport;
use App\Foundations\Modules\History\Services\InventoryHistoryService;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Seo\Services\SeoService;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryFeature;
use App\Models\Inventories\Transaction;
use App\Models\Orders;
use App\Services\Events\EventService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

final readonly class InventoryService
{
    public function __construct(
        protected SeoService $seoService,
        protected InventoryTransactionService $transactionService,
    )
    {}

    public function create(InventoryDto $dto): Inventory
    {
        return make_transaction(function () use($dto) {
            $model = $this->fill(new Inventory(), $dto);
            $model->quantity = 0;

            $model->save();

            if($dto->imageMain){
                $this->uploadImage($model, $dto->imageMain, Inventory::MAIN_IMAGE_FIELD_NAME);
            }
            if(!empty($dto->images)){
                $this->uploadImages($model, $dto->images, Inventory::GALLERY_FIELD_NAME);
            }

            $this->seoService->create($model, $dto->seoDto);

            foreach ($dto->features as $featureDto){
                $this->createInventoryFeature($model, $featureDto);
            }

            EventService::inventory($model)
                ->create()
                ->initiator(auth_user())
                ->setHistory()
                ->sendToEcomm()
                ->exec()
            ;

            $this->transactionService->create($model, $dto->purchaseDto);

            return $model;
        });
    }

    public function createInventoryFeature(Inventory $model, InventoryFeatureDto $dto): void
    {
        foreach ($dto->valueIds as $valueId){
            $m = new InventoryFeature();
            $m->inventory_id = $model->id;
            $m->feature_id = $dto->featureId;
            $m->value_id = $valueId;
            $m->save();
        }
    }

    public function removeInventoryFeature(Inventory $model): void
    {
        InventoryFeature::query()->where('inventory_id', $model->id)->delete();
    }

    public function update(Inventory $model, InventoryDto $dto): Inventory
    {
        return make_transaction(function () use($model, $dto) {
            $old = $model->dataForUpdateHistory();

            $event = EventService::inventory($model)
                ->initiator(auth_user());

            $model = $this->fill($model, $dto);

            $model->save();

            if($dto->imageMain){
                $this->deleteImage($model, Inventory::MAIN_IMAGE_FIELD_NAME);
                $this->uploadImage($model, $dto->imageMain, Inventory::MAIN_IMAGE_FIELD_NAME);
            }
            if(!empty($dto->images)){
//                $this->deleteImage($model, Inventory::GALLERY_FIELD_NAME);
                $this->uploadImages($model, $dto->images, Inventory::GALLERY_FIELD_NAME);
            }

            if($model->seo){
                $this->seoService->update($model->seo, $dto->seoDto);
            } else {
                $this->seoService->create($model, $dto->seoDto);
            }

            $this->removeInventoryFeature($model);
            foreach ($dto->features as $featureDto){
                $this->createInventoryFeature($model, $featureDto);
            }

            $event->update()
                ->setHistory($old)
                ->sendToEcomm()
                ->exec()
            ;

            return $model;
        });
    }

    protected function fill(Inventory $model, InventoryDto $dto): Inventory
    {
        $model->name = $dto->name;
        $model->slug = $dto->slug;
        $model->active = $dto->active;
        $model->stock_number = $dto->stockNumber;
        $model->article_number = $dto->articleNumber;
        $model->category_id = $dto->categoryId;
        $model->brand_id = $dto->brandId;
        $model->supplier_id = $dto->supplierId;
        $model->unit_id = $dto->unitId;
        $model->price_retail = $dto->priceRetail;
        $model->min_limit = $dto->minLimit;
        $model->min_limit_price = $dto->minLimitPrice;
        $model->notes = $dto->notes;
        $model->for_shop = $dto->forShop;
        $model->length = $dto->length;
        $model->width = $dto->width;
        $model->height = $dto->height;
        $model->weight = $dto->weight;
        $model->package_type = $dto->packageType;
        $model->is_new = $dto->isNew;
        $model->is_popular = $dto->isPopular;
        $model->is_sale = $dto->isSale;
        $model->old_price = $dto->oldPrice;
        $model->discount = $dto->discount;
        $model->delivery_cost = $dto->deliveryCost;

        return $model;
    }

    public function delete(Inventory $model): bool
    {
        if ($model->isInStock() || $model->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        if($model->seo){
            $model->seo->delete();
        }

        $clone = clone $model;
        $res = $model->delete();

        if ($res) event(new DeleteInventoryEvent($clone));

        return $res;
    }

    public function uploadImage(
        Inventory $model,
        UploadedFile $file,
        string $collection
    ): Inventory
    {
        $model->addImage($file, $collection);

        return $model;
    }

    public function uploadImages(Inventory $model, array $images = [], string $collection = 'default'): void
    {
        foreach ($images as $image) {
            $this->uploadImage($model, $image, $collection);
        }
    }

    public function deleteImage(Inventory $model,  string $collection): Inventory
    {
        $model->clearImageCollection($collection);

        return $model;
    }

    public function deleteFile(Inventory $model, int $mediaId = 0): void
    {
        if ($media = Media::find($mediaId)) {

            $media->delete();

            return;
        }

        throw new \Exception(__('exceptions.file.not_found'), Response::HTTP_NOT_FOUND);
    }

    public function linkExcelExport(Collection $models): string
    {
        $time = CarbonImmutable::now()->format('Y-m-d_H-i-s');

        $name = "excel/List_of_parts_as_at_{$time}.xlsx";

        if(Storage::disk('public')->exists($name)){
            Storage::disk('public')->delete($name);
        }

        Excel::store(new InventoryExport($models), $name,'public');

        return url("/storage/{$name}");
    }

    // for order

    public function reserveForOrder(
        Orderable $order,
        Inventory $inventory,
        float $quantity,
        ?float $price = null
    ): void
    {
        $event = EventService::inventory($inventory)
            ->setHistory([
                'order' => $order,
                'price' => $price ?? $inventory->price_retail,
                'quantity' => $quantity,
            ])
            ->custom(InventoryHistoryService::ACTION_QUANTITY_RESERVED)
            ->initiator(auth_user());

        $transaction = new Transaction();
        $transaction->operation_type = OperationType::SOLD->value;
        if($order->isPartsOrder()){
            $transaction->order_parts_id = $order->getId();
            $transaction->order_type = OrderType::Parts();
        } else {
            $transaction->order_id = $order->getId();
            $transaction->order_type = OrderType::BS();
        }
        $transaction->inventory_id = $inventory->id;
        $transaction->price = $price ?? $inventory->price_retail;
        $transaction->invoice_number = $order->getOrderNumber();
        $transaction->transaction_date = CarbonImmutable::now();
        $transaction->is_reserve = true;
        $transaction->quantity = $quantity;

        $transaction->save();

        $inventory->decreaseQuantity($transaction->quantity);

        $event->exec();
    }

    public function changeReservedQuantityForOrder(
        Orderable $order,
        Inventory $inventory,
        float $newQuantity,
        float $oldQuantity,
        ?float $price = null,
        ?float $oldPrice = null,
    ): void
    {
        if(!is_null($price) && $price !== $oldPrice){
            $this->changeReservedPriceForOrder(
                $order,
                $inventory,
                $price);
        }

        if ($newQuantity === $oldQuantity) return;

        $event = EventService::inventory($inventory)
            ->initiator(auth_user());
        $history = [
            'order' => $order,
            'price' => $price ?? $inventory->price_retail,
        ];

        $action = $newQuantity < $oldQuantity
            ? InventoryHistoryService::ACTION_QUANTITY_REDUCED
            : InventoryHistoryService::ACTION_QUANTITY_RESERVED_ADDITIONALLY;

        if($action == InventoryHistoryService::ACTION_QUANTITY_REDUCED){
            $history = array_merge($history, [
                'old_quantity' => $inventory->quantity,
                'quantity' => $inventory->quantity + ($oldQuantity - $newQuantity),
            ]);
        } else {
            $history = array_merge($history, [
                'old_quantity' => $inventory->quantity,
                'quantity' => $inventory->quantity - ($newQuantity - $oldQuantity),
            ]);
        }

        $inventory->updateTransaction(
            $order,
            $newQuantity,
            $oldQuantity,
            $price
        );

        $event
            ->setHistory($history)
            ->custom($action)->exec();
    }

    public function changeReservedPriceForOrder(
        Orders\BS\Order|Orders\Parts\Order $order,
        Inventory $inventory,
        ?float $price = null
    ): void
    {
        $inventory->changeReservedPrice($order, $price);

        EventService::inventory($inventory)
            ->setHistory([
                'order' => $order
            ])
            ->initiator(auth_user())
            ->custom(InventoryHistoryService::ACTION_PRICE_CHANGED_FOR_ORDER)
            ->exec();
    }

    public function reduceReservedInOrder(
        Orderable $order,
        Inventory $inventory,
        float $quantity,
        float $price = null,
        bool $deletedOrder = false
    ): void
    {
        $event = EventService::inventory($inventory)
            ->setHistory([
                'order' => $order,
                'quantity' => $inventory->quantity + $quantity,
                'old_quantity' => $inventory->quantity,
                'price' => $price,
            ])
            ->initiator(auth_user());

        $whereData = [
            'is_reserve' => true,
            'inventory_id' => $inventory->id,
            'quantity' => $quantity,
        ];

        if($order->isPartsOrder()){
            $whereData['order_parts_id'] = $order->getId();
        } else {
            $whereData['order_id'] = $order->getId();
        }
        $inventory->transactions()->where($whereData)->delete();

        $inventory->increaseQuantity($quantity);

        $event->custom(
            $deletedOrder
                    ? InventoryHistoryService::ACTION_QUANTITY_RETURNED
                    : InventoryHistoryService::ACTION_QUANTITY_REDUCED
        )->exec();
    }

    public function reserveOnMovingOrderFromFinished(
        Orders\BS\Order $order,
        Inventory $inventory,
        float $price
    ): void
    {
        EventService::inventory($inventory)
            ->custom(InventoryHistoryService::ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED)
            ->setHistory([
                'order' => $order
            ])
            ->initiator(auth_user())
            ->exec();

        $inventory->markAsReserve($order);
    }

    public function finishedOrderWithInventory(
        Orderable $order,
        Inventory $inventory,
        float $price,
        float $quantity
    ): void
    {
        $event = EventService::inventory($inventory)
            ->setHistory([
                'order' => $order
            ])
            ->initiator(auth_user())
        ;

        $inventory->deleteReserve($order, $price, $quantity);

        $transaction = new Transaction();
        $transaction->operation_type = OperationType::SOLD->value;
        $transaction->inventory_id = $inventory->id;
        $transaction->price = $price;
        $transaction->invoice_number = $order->getOrderNumber();
        $transaction->transaction_date = CarbonImmutable::now();
        $transaction->is_reserve = false;
        $transaction->quantity = $quantity;
        if($order->isPartsOrder()){
            $transaction->order_parts_id = $order->getId();
            $transaction->order_type = OrderType::Parts();
        } else {
            $transaction->order_id = $order->getId();
            $transaction->order_type = OrderType::BS();
        }

        $transaction->save();

        $event->custom(InventoryHistoryService::ACTION_FINISHED_ORDER_WITH_INVENTORY)
            ->exec();
    }
}
