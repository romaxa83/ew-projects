<?php

namespace App\Services\Orders\Parts;

use App\Dto\Orders\Parts\ItemDto;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Item;
use App\Models\Orders\Parts\Order;
use App\Services\Events\EventService;
use App\Services\Inventories\InventoryService;

class ItemService
{
    public function __construct(
        public InventoryService $inventoryService
    )
    {}

    public function create(
        ItemDto $dto,
        Order $order,
        bool $saveHistory = true
    ): Item
    {
        return make_transaction(function () use ($dto, $order, $saveHistory) {
            /** @var Inventory $inventory */
            $inventory = Inventory::find($dto->inventoryId);

            /** @var $itemDto ItemDto */
            $model = new Item();
            $model->order_id = $order->id;
            $model->inventory_id = $dto->inventoryId;
            $model->qty = $dto->qty;
            $model->free_shipping = $inventory->delivery_cost
                ? true
                : false
            ;
            $model->price = $inventory->price_retail;
            $model->price_old = $inventory->old_price ?? $inventory->price_retail + $inventory->delivery_cost;
            $model->delivery_cost = $inventory->delivery_cost;
            $model->discount = $dto->discount;

            $model->save();

            if(!$order->isDraft()){
                $this->inventoryService->reserveForOrder(
                    order: $order,
                    inventory: $inventory,
                    quantity: $dto->qty,
                    price: $model->getPriceForTransaction(),
                );
            }

            $order->setAmounts();

            if($saveHistory) {
                EventService::partsOrder($order)
                    ->custom(OrderPartsHistoryService::ACTION_ADD_ITEM)
                    ->initiator(auth_user())
                    ->setHistory([
                        'inventory' => $inventory,
                        'item' => $model,
                    ])
                    ->sendToEcomm(OrderPartsHistoryService::ACTION_UPDATE)
                    ->exec()
                ;
            }

            return $model;
        });
    }

    public function update(
        Item $model,
        ItemDto $dto,
        bool $saveHistory = true
    ): Item
    {
        return make_transaction(function () use ($model, $dto, $saveHistory) {
            /** @var $inventory Inventory */
            $inventory = Inventory::find($dto->inventoryId);

            // обновляем данные
            $oldQty = $model->qty;
            $oldDiscount = $model->discount;
            $model->qty = $dto->qty;
            $model->discount = $dto->discount;

            $model->save();

            if($saveHistory){
                // меня кол-во в товаре/цены в транзакции (резерв)
                $this->inventoryService->changeReservedQuantityForOrder(
                    order: $model->order,
                    inventory: $inventory,
                    newQuantity: $dto->qty,
                    oldQuantity:$oldQty,
                    price: $model->getPriceForTransaction(),
                );
            }

            $model->order->setAmounts();

            // пишем в историю
            if($saveHistory) {
                EventService::partsOrder($model->order)
                    ->custom(OrderPartsHistoryService::ACTION_UPDATE_ITEM)
                    ->initiator(auth_user())
                    ->setHistory([
                        'item' => $model,
                        'inventory' => $inventory,
                        'old_discount' => $oldDiscount,
                        'old_qty' => $oldQty,
                    ])
                    ->sendToEcomm(OrderPartsHistoryService::ACTION_UPDATE)
                    ->exec()
                ;
            }

            return $model->refresh();
        });
    }

    public function delete(Item $model, bool $saveHistory = true): bool
    {
        return make_transaction(function () use ($model, $saveHistory) {
            if($saveHistory) {
                $service = EventService::partsOrder($model->order)
                    ->custom(OrderPartsHistoryService::ACTION_DELETE_ITEM)
                    ->initiator(auth_user())
                    ->setHistory([
                        'inventory' => $model->inventory,
                        'item' => $model,
                    ])
                ;
            }

            if(!$model->order->isDraft()){
                $this->inventoryService->reduceReservedInOrder(
                    $model->order,
                    $model->inventory,
                    $model->qty,
                    $model->getPriceForTransaction(),
                    deletedOrder: true
                );
            }

            $res = $model->delete();

            $model->order->setAmounts();

            if($saveHistory){
                $service
                    ->sendToEcomm(OrderPartsHistoryService::ACTION_UPDATE)
                    ->exec();
            }

            return $res;
        });
    }
}
