<?php

namespace App\Services\Events\Order;

use App\Broadcasting\Events\Offers\NewOfferBroadcast;
use App\Broadcasting\Events\Offers\ReleaseOfferBroadcast;
use App\Broadcasting\Events\Offers\TakenOfferBroadcast;
use App\Broadcasting\Events\Orders\DeleteOrderBroadcast;
use App\Broadcasting\Events\Orders\NewOrderBroadcast;
use App\Broadcasting\Events\Orders\OrderChangeDeductBroadcast;
use App\Broadcasting\Events\Orders\OrderChangeIsPaidBroadcast;
use App\Broadcasting\Events\Orders\RestoreOrderBroadcast;
use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Events\OrderModifyEvent;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Models\PushNotifications\PushNotificationTask;
use App\Services\Events\EventService;
use App\Services\Histories\HistoryHandler;
use App\Services\Orders\OrderService;
use Illuminate\Database\Eloquent\Collection;
use TypeError;

class OrderEventService extends EventService
{
    private const ACTION_RESTORE = 'restore';
    private const ACTION_DUPLICATED = 'duplicated';
    private const ACTION_REASSIGN = 'reassign';
    private const ACTION_PAID = 'paid';
    private const ACTION_DEDUCT = 'deduct';
    public const ACTION_ADD_VEHICLE = 'add_vehicle';
    public const ACTION_DELETE_VEHICLE = 'delete_vehicle';
    public const ACTION_DELETE_EXPENSE = 'delete_expense';
    public const ACTION_DELETE_BONUS = 'delete_bonus';
    public const ACTION_DELETE_ATTACHMENT = 'delete_attachment';
    public const ACTION_DRIVER_ATTACHED_DOCUMENT = 'driver_attached_document';
    public const ACTION_DRIVER_DELETE_DOCUMENT = 'driver_delete_document';
    public const ACTION_DRIVER_ATTACHED_PHOTO = 'driver_attached_photo';
    public const ACTION_DRIVER_DELETE_PHOTO = 'driver_delete_photo';
    public const ACTION_SIGNED_INSPECTION = 'signed_inspection';
    public const ACTION_PAYMENT_STAGE_ADDED = 'payment_stage_added';
    public const ACTION_PAYMENT_STAGE_DELETED = 'payment_stage_deleted';

    private const HISTORY_MESSAGE_PAYMENT_STAGE_ADDED = 'history.payment_stage_added';
    private const HISTORY_MESSAGE_PAYMENT_STAGE_DELETED = 'history.payment_stage_deleted';
    private const HISTORY_MESSAGE_OFFER_CREATED = 'history.offer_created';
    private const HISTORY_MESSAGE_ORDER_CREATED = 'history.order_created';
    private const HISTORY_MESSAGE_ORDER_DUPLICATED = 'history.order_duplicated';
    private const HISTORY_MESSAGE_ORDER_MARKED_PAID = 'history.order_marked_paid';
    private const HISTORY_MESSAGE_ORDER_MARKED_UNPAID = 'history.order_marked_unpaid';
    private const HISTORY_MESSAGE_USER_TAKE_ORDER = 'history.user_take_order';
    private const HISTORY_MESSAGE_USER_RELEASE_ORDER = 'history.user_release_order';
    private const HISTORY_MESSAGE_ON_DEDUCT_FROM_DRIVER = 'history.on_deduct_from_driver';
    private const HISTORY_MESSAGE_ON_DEDUCT_FROM_DRIVER_WITH_NOTE = 'history.on_deduct_from_driver_with_note';
    private const HISTORY_MESSAGE_OFF_DEDUCT_FROM_DRIVER = 'history.off_deduct_from_driver';
    private const HISTORY_MESSAGE_ORDER_CHANGED = 'history.order_changed';
    private const HISTORY_MESSAGE_ORDER_DELETED = 'history.order_deleted';
    private const HISTORY_MESSAGE_ORDER_DELETED_PERMANENTLY = 'history.order_deleted_permanently';
    private const HISTORY_MESSAGE_ORDER_RESTORED = 'history.order_restored';
    private const HISTORY_MESSAGE_ADD_VEHICLE = 'history.order_add_vehicle';
    private const HISTORY_MESSAGE_DELETE_VEHICLE = 'history.order_delete_vehicle';
    private const HISTORY_MESSAGE_DELETE_EXPENSE = 'history.order_delete_expense';
    private const HISTORY_MESSAGE_DELETE_BONUS = 'history.order_delete_bonus';
    private const HISTORY_MESSAGE_DELETE_ATTACHMENT = 'history.order_delete_attachment';
    private const HISTORY_MESSAGE_DRIVER_ATTACHED_DOCUMENT = 'history.driver_attached_document';
    private const HISTORY_MESSAGE_DRIVER_DELETE_DOCUMENT = 'history.driver_delete_document';
    private const HISTORY_MESSAGE_DRIVER_ATTACHED_PHOTO = 'history.driver_attached_photo';
    private const HISTORY_MESSAGE_DRIVER_DELETED_PHOTO = 'history.driver_delete_photo';
    public const HISTORY_MESSAGE_SIGNED_INSPECTION = 'history.signed_inspection';

    private Order $order;

    private Order $orderForHandler;

    private Collection $orders;

    private ?HistoryHandler $historyHandler = null;

    private ?OrderService $orderService = null;

    private bool $isSoftDelete;

    public function __construct(?Order $order = null)
    {
        if ($order === null) {
            return;
        }

        $this->order = $order;
        $this->orderForHandler = clone $order;

        try {
            $this->historyHandler = (new HistoryHandler())->setOrigin($this->orderForHandler);
        } catch (TypeError $e) {
            $this->historyHandler = null;
        }
    }

    private function orderService(): OrderService
    {
        if ($this->orderService !== null) {
            return $this->orderService;
        }

        $this->orderService = resolve(OrderService::class);

        return $this->orderService;
    }

    private function refreshObject(): void
    {
        $this->order->refresh();

        if ($this->historyHandler === null) {
            return;
        }

        $this->historyHandler->setDirty($this->order);
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return !$this->order->dispatcher_id && !$this->order->driver_id ? self::HISTORY_MESSAGE_OFFER_CREATED : self::HISTORY_MESSAGE_ORDER_CREATED;
            case self::ACTION_DUPLICATED:
                return self::HISTORY_MESSAGE_ORDER_DUPLICATED;
            case self::ACTION_PAID:
                return $this->order->paid_at !== null ? self::HISTORY_MESSAGE_ORDER_MARKED_PAID : self::HISTORY_MESSAGE_ORDER_MARKED_UNPAID;
            case self::ACTION_PAYMENT_STAGE_ADDED:
                return self::HISTORY_MESSAGE_PAYMENT_STAGE_ADDED;
            case self::ACTION_PAYMENT_STAGE_DELETED:
                return self::HISTORY_MESSAGE_PAYMENT_STAGE_DELETED;
            case self::ACTION_DEDUCT:
                if ($this->order->deduct_from_driver === false) {
                    return self::HISTORY_MESSAGE_OFF_DEDUCT_FROM_DRIVER;
                }
                return $this->order->deducted_note !== null ? self::HISTORY_MESSAGE_ON_DEDUCT_FROM_DRIVER_WITH_NOTE : self::HISTORY_MESSAGE_ON_DEDUCT_FROM_DRIVER;
            case self::ACTION_UPDATE:
                if ($this->order->wasChanged('dispatcher_id') && !$this->orderForHandler->dispatcher_id) {
                    return self::HISTORY_MESSAGE_USER_TAKE_ORDER;
                }
                if ($this->order->wasChanged('dispatcher_id') && $this->orderForHandler->dispatcher_id !== null && $this->order->dispatcher_id === null) {
                    return self::HISTORY_MESSAGE_USER_RELEASE_ORDER;
                }
                return self::HISTORY_MESSAGE_ORDER_CHANGED;
            case self::ACTION_DELETE:
                return !$this->isSoftDelete ? self::HISTORY_MESSAGE_ORDER_DELETED_PERMANENTLY : self::HISTORY_MESSAGE_ORDER_DELETED;
            case self::ACTION_RESTORE:
                return self::HISTORY_MESSAGE_ORDER_RESTORED;
            case self::ACTION_ADD_VEHICLE:
                return self::HISTORY_MESSAGE_ADD_VEHICLE;
            case self::ACTION_DELETE_VEHICLE:
                return self::HISTORY_MESSAGE_DELETE_VEHICLE;
            case self::ACTION_DELETE_EXPENSE:
                return self::HISTORY_MESSAGE_DELETE_EXPENSE;
            case self::ACTION_DELETE_BONUS:
                return self::HISTORY_MESSAGE_DELETE_BONUS;
            case self::ACTION_DELETE_ATTACHMENT:
                return self::HISTORY_MESSAGE_DELETE_ATTACHMENT;
            case self::ACTION_DRIVER_ATTACHED_DOCUMENT:
                return self::HISTORY_MESSAGE_DRIVER_ATTACHED_DOCUMENT;
            case self::ACTION_DRIVER_DELETE_DOCUMENT:
                return self::HISTORY_MESSAGE_DRIVER_DELETE_DOCUMENT;
            case self::ACTION_DRIVER_ATTACHED_PHOTO:
                return self::HISTORY_MESSAGE_DRIVER_ATTACHED_PHOTO;
            case self::ACTION_DRIVER_DELETE_PHOTO:
                return self::HISTORY_MESSAGE_DRIVER_DELETED_PHOTO;
            case self::ACTION_SIGNED_INSPECTION:
                return self::HISTORY_MESSAGE_SIGNED_INSPECTION;
        }
        return null;
    }

    private function getHistoryMeta(string $message): array
    {
        $meta = [
            'role' => $this->user->getRoleName(),
            'load_id' => $this->order->load_id,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id
        ];

        if ($message === self::HISTORY_MESSAGE_ON_DEDUCT_FROM_DRIVER_WITH_NOTE) {
            $meta['note'] = $this->order->deducted_note;
        }
        return $meta;
    }

    private function setHistory(?array $meta = null, ?string $message = null, ?int $performed_at = null): void
    {
        $message = $message ?? $this->getHistoryMessage();

        if (!$this->isOrderUpdated($message)) {
            return;
        }

        $meta = $meta ?? $this->getHistoryMeta($message);

        event(
            new ModelChanged(
                $this->order,
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
        if ($message !== self::HISTORY_MESSAGE_ORDER_CHANGED) {
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

    public function create(): OrderEventService
    {
        $this->action = self::ACTION_CREATE;

        $this->setHistory();

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function duplicated(string $oldOrderLoadId): OrderEventService
    {
        $this->action = self::ACTION_DUPLICATED;

        $this->setHistory(['old_load_id' => $oldOrderLoadId, 'new_load_id' => $this->order->load_id]);

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function update(?string $action = null): OrderEventService
    {
        $this->action = $action ?? self::ACTION_UPDATE;

        event(new OrderUpdateEvent($this->order));

        $this->refreshObject();

        $this->setHistory();

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function signedInspection(OrderSignature $signature): OrderEventService
    {
        $this->action = self::ACTION_SIGNED_INSPECTION;

        $this->refreshObject();

        $this->setHistory([
            'first_name' => $signature->first_name,
            'last_name' => $signature->last_name,
            'email' => $signature->email,
            'location' => $signature->inspection_location
        ], null, $signature->signed_time->timestamp);

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function delete(bool $soft = true): OrderEventService
    {
        $this->action = self::ACTION_DELETE;

        $this->isSoftDelete = $soft;

        $this->setHistory();

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function restore(): OrderEventService
    {
        $this->action = self::ACTION_RESTORE;

        event(new OrderUpdateEvent($this->order));

        $this->setHistory();

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function reassign(Collection $orders): OrderEventService
    {
        $this->orders = $orders;

        $orders->map(
            function (Order $order) {
                EventService::order(clone $order)
                    ->user($this->user)
                    ->update();
            }
        );

        $this->action = self::ACTION_REASSIGN;

        return $this;
    }

    public function paid(): OrderEventService
    {
        $this->action = self::ACTION_PAID;

        event(new OrderUpdateEvent($this->order));

        $this->refreshObject();

        $this->setHistory();

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function deduct(): OrderEventService
    {
        $this->action = self::ACTION_DEDUCT;

        event(new OrderUpdateEvent($this->order));

        $this->setHistory();

        OrderModifyEvent::dispatch($this->order->id);

        return $this;
    }

    public function broadcast(): OrderEventService
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
            case self::ACTION_DUPLICATED:
                return $this->broadcastCreate();

            case self::ACTION_UPDATE:
            case self::ACTION_DELETE_VEHICLE:
            case self::ACTION_DELETE_EXPENSE:
            case self::ACTION_DELETE_BONUS:
            case self::ACTION_DELETE_ATTACHMENT:
            case self::ACTION_SIGNED_INSPECTION:
            case self::ACTION_PAYMENT_STAGE_ADDED:
            case self::ACTION_PAYMENT_STAGE_DELETED:
                return $this->broadcastUpdate();

            case self::ACTION_REASSIGN:
                return $this->broadcastReassign();

            case self::ACTION_DELETE:
                return $this->broadcastDelete();

            case self::ACTION_RESTORE:
                return $this->broadcastRestore();

            case self::ACTION_PAID:
                return $this->broadcastPaid();

            case self::ACTION_DEDUCT:
                return $this->broadcastDeduct();
        }
        return $this;
    }

    private function broadcastCreate(): OrderEventService
    {
        if ($this->order->isOffer()) {
            event(new NewOfferBroadcast($this->order->id, $this->order->carrier_id));
        } else {
            event(new NewOrderBroadcast($this->order->id, $this->order->carrier_id));
        }

        return $this;
    }

    private function broadcastUpdate(): OrderEventService
    {
        if ($this->order->isReleased()) {
            event(new ReleaseOfferBroadcast($this->order->id, $this->order->carrier_id));
        } elseif ($this->order->isTaken()) {
            event(new TakenOfferBroadcast($this->order->id, $this->order->carrier_id));
        } else {
            event(new UpdateOrderBroadcast($this->order->id, $this->order->carrier_id));
        }

        return $this;
    }

    private function broadcastReassign(): OrderEventService
    {
        $this->orders->map(
            function (Order $order) {
                event(new UpdateOrderBroadcast($order->id, $order->carrier_id));
            }
        );

        return $this;
    }

    private function broadcastDelete(): OrderEventService
    {
        event(new DeleteOrderBroadcast($this->order->id, $this->order->carrier_id));
        return $this;
    }

    private function broadcastRestore(): OrderEventService
    {
        event(new RestoreOrderBroadcast($this->order->id, $this->order->carrier_id));
        return $this;
    }

    private function broadcastPaid(): OrderEventService
    {
        event(
            new OrderChangeIsPaidBroadcast(
                $this->order->id,
                $this->order->carrier_id,
                ($this->order->paid_at !== null)
            )
        );

        return $this;
    }

    private function broadcastDeduct(): OrderEventService
    {
        event(new OrderChangeDeductBroadcast($this->order->id, $this->order->carrier_id, $this->order->deduct_from_driver));
        return $this;
    }

    public function push(): OrderEventService
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
            case self::ACTION_DUPLICATED:
                return $this->pushCreate();

            case self::ACTION_UPDATE:
            case self::ACTION_DELETE_VEHICLE:
            case self::ACTION_DELETE_EXPENSE:
            case self::ACTION_DELETE_BONUS:
            case self::ACTION_DELETE_ATTACHMENT:
                return $this->pushUpdate();

            case self::ACTION_DELETE:
                return $this->pushDelete();

            case self::ACTION_RESTORE:
                $this->pushService()->onOrderRestore($this->order);
                return $this;

            case self::ACTION_REASSIGN:
                return $this->pushReassign();
        }

        return $this;
    }

    private function pushCreate(): OrderEventService
    {
        $this->orderService()->notifyReviewers($this->order);
        $this->pushService()->onOrderStatusNew($this->order);

        return $this;
    }

    private function pushUpdate(): OrderEventService
    {
        // check if dispatcher changed
        if ($this->order->wasChanged('dispatcher_id')) {
            $this->pushService()->deleteDispatcherNotifications($this->order);
        }

        // check if driver changed
        if ($this->order->wasChanged('driver_id')) {
            $this->pushService()->deleteDriverNotifications($this->order);
        }

        if ($this->order->wasChanged('driver_id') || $this->order->wasChanged('dispatcher_id') || $this->order->wasChanged('status')) {
            // update push data depending of status
            if ($this->order->isStatusNew() || $this->order->isStatusAssigned()) {
                $this->pushService()->onOrderStatusNew($this->order);
            } elseif ($this->order->isStatusPickedUp()) {
                $this->pushService()->onOrderStatusPickedUp($this->order);
            }
        }

        if ($this->order->wasChanged('seen_by_driver') && $this->order->seen_by_driver === true) {
            $this->pushService()->markAsSent(
                $this->order,
                $this->order->driver,
                PushNotificationTask::DRIVER_NEW_ORDER_ONCE
            );
        }

        return $this;
    }

    private function pushDelete(): OrderEventService
    {
        if ($this->isSoftDelete) {
            $this->pushService()->onOrderSoftDelete($this->order->id);
        } else {
            $this->pushService()->onOrderForceDelete($this->order->id);
        }

        $this->pushService()->onDeleteOrder($this->order);

        return $this;
    }

    private function pushReassign(): OrderEventService
    {
        /**@var Order $order*/
        $order = $this->orders->first();

        $dispatcherId = $order->dispatcher_id;

        $order->refresh();

        $isChangeDispatcher = $dispatcherId !== $order->dispatcher_id;

        if ($isChangeDispatcher) {
            $this->pushService()->onReassignDispatcherOrders($this->orders);
        } else {
            $this->pushService()->onReassignDriverOrders($this->orders);
        }

        return $this;
    }
}
