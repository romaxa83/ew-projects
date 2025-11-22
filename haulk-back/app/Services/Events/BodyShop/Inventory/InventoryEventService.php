<?php

namespace App\Services\Events\BodyShop\Inventory;

use App\Events\ModelChanged;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Services\Events\EventService;
use App\Services\Histories\BodyShop\InventoryHistoryHandler;
use TypeError;

class InventoryEventService extends EventService
{
    public const ACTION_QUANTITY_RESERVED = 'quantity_reserved';
    public const ACTION_QUANTITY_RESERVED_ADDITIONALLY = 'quantity_reserved_additionally';
    public const ACTION_QUANTITY_REDUCED = 'quantity_reduced';
    public const ACTION_QUANTITY_INCREASED = 'quantity_increased';
    public const ACTION_QUANTITY_DECREASED = 'quantity_decreased';
    public const ACTION_QUANTITY_DECREASED_SOLD = 'quantity_decreased_sold';
    public const ACTION_QUANTITY_RETURNED = 'quantity_returned';

    public const ACTION_PRICE_CHANGED_FOR_ORDER = 'price_changed_for_order';
    public const ACTION_FINISHED_ORDER_WITH_INVENTORY = 'finished_order_with_inventory';
    public const ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED = 'reserve_on_moving_order_from_finished';

    private const HISTORY_MESSAGE_INVENTORY_CREATED = 'history.bs.inventory_created';
    private const HISTORY_MESSAGE_INVENTORY_CHANGED = 'history.bs.inventory_changed';

    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_INCREASED = 'history.bs.inventory_quantity_increased';
    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED = 'history.bs.inventory_quantity_decreased';
    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED_SOLD = 'history.bs.inventory_quantity_decreased_sold';
    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER = 'history.bs.inventory_quantity_reserved_for_order';
    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_ADDITIONALLY_FOR_ORDER = 'history.bs.inventory_quantity_reserved_additionally_for_order';
    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_REDUCED_FROM_ORDER = 'history.bs.inventory_quantity_reduced_from_order';
    private const HISTORY_MESSAGE_INVENTORY_PRICE_CHANGED_FOR_ORDER = 'history.bs.inventory_price_changed_for_order';
    private const HISTORY_MESSAGE_FINISHED_ORDER_WITH_INVENTORY = 'history.bs.finished_order_with_inventory';
    private const HISTORY_MESSAGE_INVENTORY_QUANTITY_RETURNED_FOR_DELETED_ORDER = 'history.bs.inventory_quantity_returned_for_deleted_order';

    private Inventory $inventory;

    private Inventory $inventoryForHandler;

    private ?InventoryHistoryHandler $historyHandler = null;

    private ?Order  $order = null;

    private ?float $price = null;

    public function __construct(?Inventory $inventory = null, ?string $comment = null, ?Order $order = null, ?float $price = null)
    {
        if ($inventory === null) {
            return;
        }

        $this->inventory = $inventory;
        $this->inventoryForHandler = clone $inventory;

        $this->order = $order;
        $this->price = $price;

        try {
            $this->historyHandler = (new InventoryHistoryHandler())
                ->setComment($comment);
        } catch (TypeError $e) {
            $this->historyHandler = null;
        }
    }

    private function refreshObject(): void
    {
        $this->inventory->refresh();

        if ($this->historyHandler === null) {
            return;
        }

        $this->historyHandler->setDirty($this->inventory);
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return  self::HISTORY_MESSAGE_INVENTORY_CREATED;
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_INVENTORY_CHANGED;
            case self::ACTION_QUANTITY_RESERVED:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER;
            case self::ACTION_QUANTITY_RESERVED_ADDITIONALLY:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_ADDITIONALLY_FOR_ORDER;
            case self::ACTION_QUANTITY_REDUCED:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_REDUCED_FROM_ORDER;
            case self::ACTION_PRICE_CHANGED_FOR_ORDER:
                return self::HISTORY_MESSAGE_INVENTORY_PRICE_CHANGED_FOR_ORDER;
            case self::ACTION_FINISHED_ORDER_WITH_INVENTORY:
                return self::HISTORY_MESSAGE_FINISHED_ORDER_WITH_INVENTORY;
            case self::ACTION_QUANTITY_INCREASED:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_INCREASED;
            case self::ACTION_QUANTITY_DECREASED:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED;
            case self::ACTION_QUANTITY_DECREASED_SOLD:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED_SOLD;
            case self::ACTION_QUANTITY_RETURNED:
                return self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RETURNED_FOR_DELETED_ORDER;

        }

        return null;
    }

    private function getHistoryMeta(): array
    {
        $meta = [
            'role' => trans('history.bs.' . $this->user->getRoleName()),
            'stock_number' => $this->inventory->stock_number,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
        ];

        if (in_array(
            $this->action,
            [
                self::ACTION_QUANTITY_REDUCED,
                self::ACTION_QUANTITY_RESERVED,
                self::ACTION_QUANTITY_RESERVED_ADDITIONALLY,
                self::ACTION_PRICE_CHANGED_FOR_ORDER,
                self::ACTION_FINISHED_ORDER_WITH_INVENTORY,
                self::ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED,
                self::ACTION_QUANTITY_RETURNED
            ])
        ) {
            $meta['price'] = '$' . number_format($this->price ?? $this->inventory->price_retail, 2);
            $meta['order_link'] = $this->order ? str_replace('{id}', $this->order->id, config('frontend.bs_order_show_url')) : '';
            $meta['order_number'] = $this->order->order_number ?? '';
            $meta['inventory_name'] = $this->inventory->name;
        }

        if (in_array(
            $this->action,
            [
                self::ACTION_QUANTITY_INCREASED,
                self::ACTION_QUANTITY_DECREASED,
                self::ACTION_QUANTITY_DECREASED_SOLD
            ]
        )) {
            $meta['inventory_name'] = $this->inventory->name;
        }

        return $meta;
    }

    private function setHistory(?array $meta = null, ?string $message = null, ?int $performed_at = null): void
    {
        $message = $message ?? $this->getHistoryMessage();

        if (!$this->isOrderUpdated($message)) {
            return;
        }

        $meta = $meta ?? $this->getHistoryMeta();

        event(
            new ModelChanged(
                $this->inventory,
                $message,
                $meta,
                $performed_at,
                null,
                $this->historyHandler
            )
        );
    }

    private function isOrderUpdated($message): bool
    {
        if ($message === self::HISTORY_MESSAGE_INVENTORY_CREATED) {
            return true;
        }

        if ($this->historyHandler === null) {
            return false;
        }

        $comparisons = $this->historyHandler->start();

        if (empty($comparisons)) {
            return false;
        }

        return true;
    }

    public function create(): InventoryEventService
    {
        $this->action = self::ACTION_CREATE;

        if ($this->historyHandler) {
            $this->historyHandler->setDirty($this->inventoryForHandler);
        }

        $this->setHistory();

        return $this;
    }

    public function update(?string $action = null): InventoryEventService
    {
        $this->action = $action ?? self::ACTION_UPDATE;

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin($this->inventoryForHandler);
        }

        $this->refreshObject();

        $this->setHistory();

        return $this;
    }

    public function changePriceForOrder(): InventoryEventService
    {
        $this->action = self::ACTION_PRICE_CHANGED_FOR_ORDER;

        event(
            new ModelChanged(
                $this->inventory,
                self::HISTORY_MESSAGE_INVENTORY_PRICE_CHANGED_FOR_ORDER,
                $this->getHistoryMeta(),
                null,
                null,
                null
            )
        );

        return $this;
    }

    public function finishedOrderWithInventory(): InventoryEventService
    {
        $this->action = self::ACTION_FINISHED_ORDER_WITH_INVENTORY;

        event(
            new ModelChanged(
                $this->inventory,
                self::HISTORY_MESSAGE_FINISHED_ORDER_WITH_INVENTORY,
                $this->getHistoryMeta(),
                null,
                null,
                null
            )
        );

        return $this;
    }

    public function reserveOnMovingOrderFromFinished(): InventoryEventService
    {
        $this->action = self::ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED;

        event(
            new ModelChanged(
                $this->inventory,
                self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER,
                $this->getHistoryMeta(),
                null,
                null,
                null
            )
        );

        return $this;
    }
}
