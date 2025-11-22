<?php

namespace App\Services\Orders\Parts;

use App\Dto\Orders\Parts\DeliveryDto;
use App\Enums\Orders\Parts\DeliveryStatus;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Exceptions\Orders\Parts\ChangeOrderStatusException;
use App\Foundations\Modules\History\Services\InventoryHistoryService;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Http\Controllers\Api\V1\Inventories\Inventory\TransactionController;
use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Item;
use App\Models\Orders\Parts\Order;
use App\Services\Events\EventService;
use App\Services\Inventories\InventoryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

class OrderStatusService
{
    public function __construct(
        protected DeliveryService $deliveryService,
    )
    {}

    public function changeStatus(
        Order $model,
        OrderStatus|string $status,
        bool $save = true,
        bool $throwException = true,
        bool $saveHistory = false,
        array $additionalData = []
    ): ?Order
    {
        $status = $status instanceof OrderStatus
            ? $status
            : OrderStatus::from($status);

        return make_transaction(function () use ($model, $status, $save, $throwException, $saveHistory, $additionalData) {
            $oldStatus = $model->status;

            $order = $this->toggleStatus($model, $status, $save, additionalData: $additionalData);

            if($saveHistory) {
                EventService::partsOrder($order)
                    ->initiator(auth_user())
                    ->custom(OrderPartsHistoryService::ACTION_STATUS_CHANGED)
                    ->setHistory([
                        'old_status' => $oldStatus,
                        'data' => $additionalData,
                    ])
                    ->sendToEcomm()
                    ->exec()
                ;
            }

            return $order;

        });
    }

    private function toggleStatus(
        Order $model,
        OrderStatus|string $status,
        bool $save = true,
        bool $throwException = true,
        array $additionalData = []
    ): ?Order
    {

        if(!$model->status){
            return match ($status) {
                OrderStatus::New => $this->toNew($model, $save),
                OrderStatus::In_process => $this->toInProgress($model, $save),
                default => $throwException
                    ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
                    : null
            };
        }

        if($model->status->isNew()){
            return match ($status) {
                OrderStatus::In_process => $this->toInProgress($model, $save),
                OrderStatus::Canceled => $this->toCanceled($model, $save),
                default => $throwException
                    ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
                    : null
            };
        }
        if($model->status->isInProcess()){
            return match ($status) {
                OrderStatus::Pending_pickup => $this->toPendingPickup($model, $save),
                OrderStatus::Canceled => $this->toCanceled($model, $save),
                OrderStatus::Sent => $this->toSent($model, $additionalData, $save),
                default => $throwException
                    ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
                    : null
            };
        }
        if($model->status->isPendingPickup()){
            return match ($status) {
                OrderStatus::Delivered => $this->toDelivered($model, $save),
                default => $throwException
                    ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
                    : null
            };
        }
        if($model->status->isSent()){
            return match ($status) {
                OrderStatus::Delivered => $this->toDelivered($model, $save),
                OrderStatus::Lost => $this->toLost($model, $save),
                OrderStatus::Damaged => $this->toDamaged($model, $save),
                default => $throwException
                    ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
                    : null
            };
        }
        if($model->status->isDelivered()){
            return match ($status) {
                OrderStatus::Returned => $this->toReturned($model, $save),
                default => $throwException
                    ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
                    : null
            };
        }

        return $throwException
            ? throw new ChangeOrderStatusException(code:Response::HTTP_UNPROCESSABLE_ENTITY)
            : null;
    }


    private function toNew(Order $model, bool $save = true): Order
    {
        $model = $this->setStatus($model, OrderStatus::New);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function toInProgress(Order $model, bool $save = true): Order
    {
        $model = $this->setStatus($model, OrderStatus::In_process);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function toSent(Order $model, array $additionalData = [], bool $save = true): Order
    {
        if(
            $model->payment_terms?->isImmediately()
            && !$model->isPaid()
        ){
            throw new ChangeOrderStatusException(
                code:Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $model = $this->setStatus($model, OrderStatus::Sent);

        if($save){
            $model->save();
        }

        foreach ($additionalData['sent_data'] ?? [] as $value) {
            $this->deliveryService->create(DeliveryDto::byArgs($value), $model);
        }

        return $model;
    }

    private function toPendingPickup(Order $model, bool $save = true): Order
    {
        if(!$model->delivery_type->isPickup()){
            throw new ChangeOrderStatusException(
                __('exceptions.orders.if_status_change_shipping_method_must_be_pickup'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $model = $this->setStatus($model, OrderStatus::Pending_pickup);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function toCanceled(Order $model, bool $save = true): Order
    {
        return make_transaction(function () use ($model, $save) {
            $model = $this->setStatus($model, OrderStatus::Canceled);

            foreach ($model->items as $item){
                $old = $item->inventory->dataForUpdateHistory();
                $event = EventService::inventory($item->inventory)
                    ->initiator(auth_user())
                ;

                $item->inventory->quantity += $item->qty;
                $item->inventory->save();
                $item->qty = 0;
                $item->save();

                Transaction::query()
                    ->where('is_reserve', true)
                    ->where('inventory_id', $item->inventory->id)
                    ->where('invoice_number', $model->order_number)
                    ->delete();

                $event->custom(InventoryHistoryService::ACTION_QUANTITY_INCREASED)
                    ->setHistory($old)
                    ->exec();
            }

            if($save){
                $model->save();
            }

            return $model;
        });
    }

    private function toDamaged(Order $model, bool $save = true): Order
    {
        $model = $this->setStatus($model, OrderStatus::Damaged);

        foreach ($model->deliveries as $delivery){
            /** @var $delivery Delivery */
            if(!$delivery->status->isDelivered()){
                $this->deliveryService->setStatus($delivery, DeliveryStatus::Delivered, true);
            }
        }

        $this->transactionFinished($model);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function toReturned(Order $model, bool $save = true): Order
    {
        $days = config('orders.parts.change_status_delivered_to_returned');
        $finalDate = CarbonImmutable::now()->subDays($days);

        if($model->status_changed_at < $finalDate){
            throw new ChangeOrderStatusException(
                code:Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $model = $this->setStatus($model, OrderStatus::Returned);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function toLost(Order $model, bool $save = true): Order
    {
        $model = $this->setStatus($model, OrderStatus::Lost);

        $this->transactionFinished($model);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function toDelivered(Order $model, bool $save = true): Order
    {
        $now = CarbonImmutable::now();

        $model->load(['items.inventory']);
        $model = $this->setStatus($model, OrderStatus::Delivered);

        if($model->payment_terms?->isDay15()){
            $model->past_due_at = $now->addHours(config('orders.parts.over_due.'.PaymentTerms::Day_15()));
        }
        if($model->payment_terms?->isDay30()){
            $model->past_due_at = $now->addHours(config('orders.parts.over_due.'.PaymentTerms::Day_30()));
        }

        $model->delivered_at = $now;

        foreach ($model->deliveries as $delivery){
            /** @var $delivery Delivery */
            if(!$delivery->status->isDelivered()){
                $this->deliveryService->setStatus($delivery, DeliveryStatus::Delivered, true);
            }
        }

        $this->transactionFinished($model);

        if($save){
            $model->save();
        }

        return $model;
    }

    private function setStatus(
        Order $model,
        OrderStatus $status
    ): Order
    {
        $model->status = $status;
        $model->status_changed_at = CarbonImmutable::now();

        return $model;
    }

    private function transactionFinished(Order $model): void
    {
        $inventoryService = resolve(InventoryService::class);
        foreach ($model->items as $item){
            /** @var $item Item */
            $inventoryService->finishedOrderWithInventory(
                order: $model,
                inventory:$item->inventory,
                price: $item->getPriceForTransaction(),
                quantity: $item->qty,
            );
        }
    }

    public function checkOrderAndSetDelivered(Order $order): void
    {
        $builder = $order->deliveries();
        if ($builder->count() === $builder->where('status', DeliveryStatus::Delivered)->count()) {
            $this->changeStatus(
                $order,
                OrderStatus::Delivered,
                saveHistory: true
            );
        }
    }
}
