<?php


namespace App\Services\Events\Order;


use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Events\OrderModifyEvent;
use App\Events\Orders\OrderUpdateEvent;
use App\Events\OrderStatusChanged;
use App\Models\Orders\Order;
use App\Notifications\Alerts\AlertNotification;
use App\Services\Events\EventService;

class OrderStatusEventService extends EventService
{
    private const HISTORY_MESSAGE_STATUS_CHANGED_MANUALLY = 'history.status_changed_manually';
    private const HISTORY_MESSAGE_ORDER_DELIVERED = 'history.order_delivered';
    private const HISTORY_MESSAGE_ORDER_PICKED_UP = 'history.order_picked_up';

    private Order $order;

    private bool $isManually = true;

    private ?string $oldStatus = null;

    private ?string $newStatus = null;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function auto(): OrderStatusEventService
    {
        $this->isManually = false;

        return $this;
    }

    public function old(string $status): OrderStatusEventService
    {
        $this->oldStatus = $status;
        return $this;
    }

    private function getHistoryMessage(): ?string
    {
        if ($this->order->status === Order::STATUS_DELIVERED) {
            return $this->isManually ? self::HISTORY_MESSAGE_STATUS_CHANGED_MANUALLY : self::HISTORY_MESSAGE_ORDER_DELIVERED;
        }
        if ($this->order->status === Order::STATUS_PICKED_UP) {
            return $this->isManually ? self::HISTORY_MESSAGE_STATUS_CHANGED_MANUALLY : self::HISTORY_MESSAGE_ORDER_PICKED_UP;
        }
        return self::HISTORY_MESSAGE_STATUS_CHANGED_MANUALLY;
    }

    private function getHistoryMeta(): array
    {
        $meta = [
            'role' => $this->user->getRoleName(),
            'load_id' => $this->order->load_id,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
        ];
        if ($this->isManually) {
            $meta['status_old'] = Order::STATUSES_LABEL[$this->oldStatus];
            $meta['status_new'] = Order::STATUSES_LABEL[$this->newStatus];
        }

        return $meta;
    }

    private function getPerformedAt(): ?int
    {
        if ($this->order->status === Order::STATUS_DELIVERED) {
            return $this->isManually ? null: $this->order->delivery_date_actual;
        }
        if ($this->order->status === Order::STATUS_PICKED_UP) {
            return $this->isManually ? null: $this->order->pickup_date_actual;
        }

        return null;
    }

    private function setHistory(): void
    {
        event(
            new ModelChanged(
                $this->order,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
                $this->getPerformedAt(),
            )
        );
    }

    public function change(?string $newStatus = null): OrderStatusEventService
    {
        $this->newStatus = $newStatus;

        $this->setHistory();

        if ($this->isManually) {
            event(new OrderUpdateEvent($this->order));
        } else {
            event(new OrderStatusChanged($this->order));
        }
        OrderModifyEvent::dispatch($this->order->id);
        return $this;
    }

    public function push(): OrderStatusEventService
    {
        if ($this->order->status === Order::STATUS_PICKED_UP && $this->isManually === false) {
            $this->pushService()->onOrderStatusPickedUp($this->order);
            return $this;
        }
        if ($this->oldStatus === Order::CALCULATED_STATUS_DELIVERED && $this->newStatus === Order::CALCULATED_STATUS_PICKED_UP) {
            $this->pushService()->deleteDriverPush($this->order, 'driver_order_to_picked_up');
            $this->pushService()->pushDriver($this->order, 'driver_order_to_picked_up');
            return $this;
        }
        if ($this->oldStatus === Order::CALCULATED_STATUS_PICKED_UP && $this->newStatus === Order::CALCULATED_STATUS_DELIVERED) {
            $this->pushService()->deleteDriverPush($this->order, 'driver_order_to_delivered');
            $this->pushService()->pushDriver($this->order, 'driver_order_to_delivered');
            return $this;
        }
        return $this;
    }

    public function broadcast(): OrderStatusEventService
    {
        event(new UpdateOrderBroadcast($this->order->id, $this->order->carrier_id));

        if ($this->order->user) {
            $this->order->user->notify(
                new AlertNotification(
                    $this->order->user->getCompanyId(),
                    $this->getHistoryMessage(),
                    AlertNotification::TARGET_TYPE_ORDER,
                    ['order_id' => $this->order->id,],
                    $this->getHistoryMeta()
                )
            );
        }

        return $this;
    }
}
