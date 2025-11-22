<?php

namespace App\Services\BodyShop\Inventories;

use App\Dto\BodyShop\Inventories\TransactionDto;
use App\Dto\BodyShop\Inventories\InventoryDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\History\History;
use App\Models\Users\User;
use App\Services\Events\BodyShop\Inventory\InventoryEventService;
use App\Services\Events\EventService;
use DB;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryService
{
    private ?User $user = null;

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function create(InventoryDto $dto): Inventory
    {
        try {
            DB::beginTransaction();

            /** @var Inventory $inventory */
            $inventory = Inventory::query()->make($dto->getInventoryData() + ['quantity' => 0]);
            $inventory->saveOrFail();

            EventService::bsInventory($inventory)
                ->user($this->user)
                ->create();

            $this->account($inventory, $dto->getPurchaseData());

            DB::commit();

            return $inventory;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Inventory $inventory, InventoryDto $dto): Inventory
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsInventory($inventory);

            $inventory->update($dto->getInventoryData());

            DB::commit();

            $event->user($this->user)
                ->update();

            return $inventory;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function getHistory(int $inventoryId): Collection
    {
        $history = History::query()
            ->where(
                [
                    ['model_id', $inventoryId],
                    ['model_type', Inventory::class],
                ]
            )
            ->latest('performed_at')
            ->get();

        if ($history) {
            foreach ($history as &$h) {
                if (isset($h['meta']) && is_array($h['meta'])) {
                    $h['message'] = trans($h['message'], $h['meta']);
                }
            }
        }

        return $history;
    }

    public function getHistoryDetailed(int $inventoryId, array $filters, int $perPage): LengthAwarePaginator
    {
        $history = History::query()
            ->where(
                [
                    ['model_id', $inventoryId],
                    ['model_type', Inventory::class],
                ]
            )
            ->whereType(History::TYPE_CHANGES)
            ->filter($filters)
            ->latest('id')
            ->paginate($perPage);

        if ($history) {
            foreach ($history->items() as &$h) {
                if (isset($h['meta']) && is_array($h['meta'])) {
                    $h['message'] = trans($h['message'], $h['meta']);
                }
            }
        }

        return $history;
    }

    public function reserveForOrder(
        Order $order,
        Inventory $inventory,
        float $quantity,
        ? float $price = null
    ): void {
        $event = EventService::bsInventory($inventory, null, $order, $price ?? $inventory->price_retail)
            ->user($this->user);

        $inventory->addTransaction([
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'order_id' => $order->id,
            'price' => $price ?? $inventory->price_retail,
            'invoice_number' => $order->order_number,
            'transaction_date' => now(),
            'is_reserve' => true,
            'quantity' => $quantity,
        ]);

        $event->update(InventoryEventService::ACTION_QUANTITY_RESERVED);
    }

    public function changeReservedQuantityForOrder(
        Order $order,
        Inventory $inventory,
        float $newQuantity,
        float $oldQuantity,
        ? float $price
    ): void {
        if ($newQuantity === $oldQuantity) {
            return;
        }

        $event = EventService::bsInventory($inventory, null, $order, $price ?? $inventory->price_retail)
            ->user($this->user);

        $inventory->updateTransaction($order->id, $newQuantity, $oldQuantity);

        $action = $newQuantity < $oldQuantity
            ? InventoryEventService::ACTION_QUANTITY_REDUCED
            : InventoryEventService::ACTION_QUANTITY_RESERVED_ADDITIONALLY;

        $event->update($action);
    }

    public function changeReservedPriceForOrder(
        Order $order,
        Inventory $inventory
    ): void {
        $inventory->changeReservedPrice($order);
        $event = EventService::bsInventory($inventory, null, $order)
            ->user($this->user)
            ->changePriceForOrder();
    }

    public function finishedOrderWithInventory(
        Order $order,
        Inventory $inventory,
        float $price,
        float $quantity
    ): void {
        $event = EventService::bsInventory($inventory, null, $order, $price)
            ->user($this->user);

        $inventory->deleteReserve($order, $price, $quantity);
        $inventory->addTransaction(
            [
                'operation_type' => Transaction::OPERATION_TYPE_SOLD,
                'order_id' => $order->id,
                'price' => $price,
                'invoice_number' => $order->order_number,
                'transaction_date' => now(),
                'quantity' => $quantity,
                'is_reserve' => false,
            ],
            true
        );

        $event->finishedOrderWithInventory();
    }

    public function reserveOnMovingOrderFromFinished(
        Order $order,
        Inventory $inventory,
        float $price
    ): void {
        $event = EventService::bsInventory($inventory, null, $order, $price)
            ->user($this->user);

        $inventory->markAsReserve($order);

        $event->reserveOnMovingOrderFromFinished();
    }

    public function account(Inventory $inventory, TransactionDto $data): Transaction
    {
        $event = EventService::bsInventory($inventory, $data->getData()['comment'] ?? null)
            ->user($this->user);

        $transaction = $inventory->addTransaction($data->getData());

        if ($transaction->isPurchase()) {
            $action = InventoryEventService::ACTION_QUANTITY_INCREASED;
        } else {
            $action = InventoryEventService::ACTION_QUANTITY_DECREASED;
            if ($transaction->describe === Transaction::DESCRIBE_SOLD) {
                $action = InventoryEventService::ACTION_QUANTITY_DECREASED_SOLD;
            }
        }

        $event->update($action);

        return $transaction;
    }

    public function reduceReservedInOrder(
        Order $order,
        Inventory $inventory,
        float $quantity,
        float $price = null,
        bool $deletedOrder = false
    ): void {
        $event = EventService::bsInventory($inventory, null, $order, $price)
            ->user($this->user);

        $inventory->transactions()->where([
            'is_reserve' => true,
            'order_id' => $order->id,
            'inventory_id' => $inventory->id,
            'quantity' => $quantity,
        ])->delete();

        $inventory->increaseQuantity($quantity);

        $event->update(
            $deletedOrder
                ? InventoryEventService::ACTION_QUANTITY_RETURNED
                : InventoryEventService::ACTION_QUANTITY_REDUCED
        );
    }

    public function destroy(Inventory $inventory): Inventory
    {
        if ($inventory->isInStock() || $inventory->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        $inventory->delete();

        return $inventory;
    }
}
