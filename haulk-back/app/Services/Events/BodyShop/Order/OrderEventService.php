<?php

namespace App\Services\Events\BodyShop\Order;

use App\Events\ModelChanged;
use App\Models\BodyShop\Orders\Order;
use App\Services\Events\EventService;
use App\Services\Histories\BodyShop\OrderHistoryHandler;
use App\Services\Histories\HistoryHandlerInterface;
use TypeError;

class OrderEventService extends EventService
{
    public const ACTION_DELETE_ATTACHMENT = 'delete_attachment';
    public const ACTION_ATTACHED_DOCUMENT = 'attached_document';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_REASSIGNED_MECHANIC = 'reassigned_mechanic';
    public const ACTION_RESTORE = 'restore';
    public const ACTION_SEND_DOCS = 'send_docs';
    public const ACTION_CREATE_PAYMENT = 'create_payment';
    public const ACTION_DELETE_PAYMENT = 'delete_payment';

    private const HISTORY_MESSAGE_ORDER_CREATED = 'history.bs.order_created';
    private const HISTORY_MESSAGE_ORDER_CHANGED = 'history.bs.order_changed';
    private const HISTORY_MESSAGE_DELETE_ATTACHMENT = 'history.bs.order_delete_attachment';
    private const HISTORY_MESSAGE_ATTACHED_DOCUMENT = 'history.bs.attached_document';
    private const HISTORY_MESSAGE_STATUS_CHANGED = 'history.bs.status_changed';
    private const HISTORY_MESSAGE_REASSIGNED_MECHANIC = 'history.bs.order_reassigned_mechanic';
    private const HISTORY_MESSAGE_ORDER_DELETED = 'history.bs.order_deleted';
    private const HISTORY_MESSAGE_ORDER_RESTORED = 'history.bs.order_restored';
    private const HISTORY_MESSAGE_ORDER_SEND_DOCS = 'history.bs.order_send_docs';
    private const HISTORY_MESSAGE_ORDER_CREATED_PAYMENT = 'history.bs.order_created_payment';
    private const HISTORY_MESSAGE_ORDER_DELETED_PAYMENT = 'history.bs.order_deleted_payment';

    private Order $order;

    private Order $orderForHandler;

    private ?HistoryHandlerInterface $historyHandler = null;

    public function __construct(?Order $order = null)
    {
        if ($order === null) {
            return;
        }

        $this->order = $order;
        $this->orderForHandler = clone $order;

        try {
            $this->historyHandler = (new OrderHistoryHandler())->setOrigin($this->orderForHandler);
        } catch (TypeError $e) {
            $this->historyHandler = null;
        }
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
                return self::HISTORY_MESSAGE_ORDER_CREATED;
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_ORDER_CHANGED;
            case self::ACTION_DELETE_ATTACHMENT:
                return self::HISTORY_MESSAGE_DELETE_ATTACHMENT;
            case self::ACTION_ATTACHED_DOCUMENT:
                return self::HISTORY_MESSAGE_ATTACHED_DOCUMENT;
            case self::ACTION_STATUS_CHANGED:
                return self::HISTORY_MESSAGE_STATUS_CHANGED;
            case self::ACTION_REASSIGNED_MECHANIC:
                return self::HISTORY_MESSAGE_REASSIGNED_MECHANIC;
            case self::ACTION_DELETE:
                return self::HISTORY_MESSAGE_ORDER_DELETED;
            case self::ACTION_RESTORE:
                return self::HISTORY_MESSAGE_ORDER_RESTORED;
            case self::ACTION_CREATE_PAYMENT:
                return self::HISTORY_MESSAGE_ORDER_CREATED_PAYMENT;
            case self::ACTION_DELETE_PAYMENT:
                return self::HISTORY_MESSAGE_ORDER_DELETED_PAYMENT;
        }

        return null;
    }

    private function getHistoryMeta(array $additionalData = []): array
    {
        $meta = [
            'role' =>trans('history.bs.' . $this->user->getRoleName()),
            'order_number' => $this->order->order_number,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
        ];

        if ($this->action === self::ACTION_STATUS_CHANGED) {
            $meta['status'] = trans('history.bs.order_status.' . $this->order->status);
        }

        if ($this->action === self::ACTION_REASSIGNED_MECHANIC) {
            $meta['order_number'] = $this->order->order_number;
            $meta['mechanic_name'] = $this->order->mechanic->full_name;
        }

        if ($this->action === self::ACTION_SEND_DOCS) {
            $meta['receivers'] = implode(', ', $additionalData['receivers'] ?? []);
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
                $this->order,
                $message,
                $meta,
                $performed_at,
                null,
                $this->historyHandler
            )
        );
    }

    private function isOrderUpdated(string $message): bool
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

        return $this;
    }

    public function update(?string $action = null): OrderEventService
    {
        $this->action = $action ?? self::ACTION_UPDATE;

        $this->refreshObject();

        $this->setHistory();

        return $this;
    }

    public function delete(): OrderEventService
    {
        $this->action = self::ACTION_DELETE;

        event(
            new ModelChanged(
                $this->order,
                self::HISTORY_MESSAGE_ORDER_DELETED,
                $this->getHistoryMeta(),
                null,
                null,
                null
            )
        );

        return $this;
    }

    public function restore(): OrderEventService
    {
        $this->action = self::ACTION_RESTORE;

        event(
            new ModelChanged(
                $this->order,
                self::HISTORY_MESSAGE_ORDER_RESTORED,
                $this->getHistoryMeta(),
                null,
                null,
                null
            )
        );

        return $this;
    }

    public function sendDocs(array $receivers): OrderEventService
    {
        $this->action = self::ACTION_SEND_DOCS;

        event(
            new ModelChanged(
                $this->order,
                self::HISTORY_MESSAGE_ORDER_SEND_DOCS,
                $this->getHistoryMeta(['receivers' => $receivers]),
                null,
                null,
                null
            )
        );

        return $this;
    }
}
