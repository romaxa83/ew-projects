<?php

namespace App\Foundations\Modules\History\Services;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Strategies\Details;
use App\Foundations\Modules\History\Strategies\Details\DetailsStrategy;
use App\Models\Orders\Parts\Order;

class OrderPartsHistoryService extends HistoryService
{
    public const ACTION_ASSIGN_SALES_MANAGER = 'assign_sales_manager';
    public const ACTION_REASSIGN_SALES_MANAGER = 'reassign_sales_manager';
    public const ACTION_ADD_ITEM = 'add_item';
    public const ACTION_UPDATE_ITEM = 'update_item';
    public const ACTION_DELETE_ITEM = 'delete_item';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_REFUNDED = 'refunded';

    public const ACTION_UPDATE_DELIVERY = 'update_delivery';

    public const ACTION_CREATE_PAYMENT = 'create_payment';
    public const ACTION_DELETE_PAYMENT = 'delete_payment';

    public const ACTION_IS_PAID = 'action_is_paid';

    public const ACTION_SEND_DOCS = 'send_docs';
    public const ACTION_SEND_PAYMENT_LINK = 'send_payment_link';

    public const HISTORY_MSG_CREATED = 'history.order.common.created';
    public const HISTORY_MSG_UPDATE = 'history.order.common.updated';
    public const HISTORY_MSG_ASSIGN_SALES_MANAGER = 'history.order.parts.assign_sales_manager';
    public const HISTORY_MSG_REASSIGN_SALES_MANAGER = 'history.order.parts.reassign_sales_manager';
    public const HISTORY_MSG_ADD_ITEM = 'history.order.parts.add_item';
    public const HISTORY_MSG_UPDATE_ITEM = 'history.order.parts.update_item';
    public const HISTORY_MSG_DELETE_ITEM = 'history.order.parts.delete_item';
    public const HISTORY_MSG_STATUS_CHANGED = 'history.order.common.status_changed';
    public const HISTORY_MSG_REFUNDED = 'history.order.parts.refunded';
    public const HISTORY_MSG_CREATE_PAYMENT = 'history.order.common.created_payment';
    public const HISTORY_MSG_DELETE_PAYMENT = 'history.order.common.deleted_payment';
    public const HISTORY_MSG_UPDATE_DELIVERY = 'history.order.parts.update_delivery';

    public const HISTORY_MSG_ORDER_SEND_DOCS = 'history.order.common.send_docs';
    public const HISTORY_MSG_ORDER_SEND_PAYMENT_LINK = 'history.order.parts.send_payment_link';

    public function __construct()
    {}

    protected function getHistoryType(): string
    {
        if(
            $this->action === self::ACTION_SEND_DOCS
            || $this->action === self::ACTION_SEND_PAYMENT_LINK
        ){
            return HistoryType::ACTIVITY;
        }

        return HistoryType::CHANGES;
    }

    protected function getMsg(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => self::HISTORY_MSG_CREATED,
            self::ACTION_UPDATE => self::HISTORY_MSG_UPDATE,
            self::ACTION_ADD_ITEM => self::HISTORY_MSG_ADD_ITEM,
            self::ACTION_UPDATE_ITEM => self::HISTORY_MSG_UPDATE_ITEM,
            self::ACTION_DELETE_ITEM => self::HISTORY_MSG_DELETE_ITEM,
            self::ACTION_STATUS_CHANGED => self::HISTORY_MSG_STATUS_CHANGED,
            self::ACTION_REFUNDED => self::HISTORY_MSG_REFUNDED,
            self::ACTION_ASSIGN_SALES_MANAGER => self::HISTORY_MSG_ASSIGN_SALES_MANAGER,
            self::ACTION_REASSIGN_SALES_MANAGER => self::HISTORY_MSG_REASSIGN_SALES_MANAGER,
            self::ACTION_COMMENT_CREATED => self::HISTORY_MESSAGE_COMMENT_CREATED,
            self::ACTION_COMMENT_DELETED => self::HISTORY_MESSAGE_COMMENT_DELETED,
            self::ACTION_CREATE_PAYMENT => self::HISTORY_MSG_CREATE_PAYMENT,
            self::ACTION_DELETE_PAYMENT => self::HISTORY_MSG_DELETE_PAYMENT,
            self::ACTION_UPDATE_DELIVERY => self::HISTORY_MSG_UPDATE_DELIVERY,
            self::ACTION_SEND_DOCS => self::HISTORY_MSG_ORDER_SEND_DOCS,
            self::ACTION_SEND_PAYMENT_LINK => self::HISTORY_MSG_ORDER_SEND_PAYMENT_LINK,
        };
    }

    protected function getDetailsStrategy(): DetailsStrategy
    {
        /** @var $model Order */
        $model = $this->model;

        return match ($this->action) {
            self::ACTION_CREATE => new Details\Order\Parts\CreateStrategy($model),
            self::ACTION_UPDATE => new Details\Order\Parts\UpdateStrategy($model, $this->additional),
            self::ACTION_ADD_ITEM => new Details\Order\Parts\AddItemStrategy($model, $this->additional),
            self::ACTION_UPDATE_ITEM => new Details\Order\Parts\UpdateItemStrategy($model, $this->additional),
            self::ACTION_DELETE_ITEM => new Details\Order\Parts\DeleteItemStrategy($model, $this->additional),
            self::ACTION_STATUS_CHANGED => new Details\Order\Parts\ChangeStatusStrategy($model, $this->additional),
            self::ACTION_REFUNDED => new Details\Order\Parts\RefundedStrategy($model),
            self::ACTION_ASSIGN_SALES_MANAGER => new Details\Order\Parts\AssignSalesManagerStrategy($model, $this->additional),
            self::ACTION_REASSIGN_SALES_MANAGER => new Details\Order\Parts\AssignSalesManagerStrategy($model, $this->additional),
            self::ACTION_COMMENT_CREATED => new Details\Comment\CreateStrategy($this->comment),
            self::ACTION_COMMENT_DELETED => new Details\Comment\DeleteStrategy($this->comment),
            self::ACTION_CREATE_PAYMENT => new Details\Order\Parts\CreatePaymentStrategy($model, $this->additional),
            self::ACTION_DELETE_PAYMENT => new Details\Order\Parts\DeletePaymentStrategy($model, $this->additional),
            self::ACTION_UPDATE_DELIVERY => new Details\Order\Parts\UpdateDeliveryStrategy($model, $this->additional),
            default => new Details\DummyStrategy(),
        };
    }

    protected function getMsgAttr(): array
    {
        /** @var $order Order */
        $order = $this->model;

        $meta = [
            'role' => $this->user?->role_name_pretty,
            'email' => $this->user?->email->getValue(),
            'full_name' => $this->user?->full_name,
            'user_id' => $this->user?->id,
            'order_number' => $order->order_number,
            'order_id' => $order->id,
        ];

        if(
            $this->action === self::ACTION_ASSIGN_SALES_MANAGER
            || $this->action === self::ACTION_REASSIGN_SALES_MANAGER
        ){
            $meta['sales_manager_name'] = $order->salesManager->full_name;
        }

        if($this->action === self::ACTION_STATUS_CHANGED){
            $meta['status'] = $order->status->value;
        }

        if(
            $this->action === self::ACTION_ADD_ITEM
            || $this->action === self::ACTION_UPDATE_ITEM
            || $this->action === self::ACTION_DELETE_ITEM
        ){
            $meta['inventory_name'] = $this->additional['inventory']?->name;
        }
        if ($this->action === self::ACTION_SEND_DOCS) {
            $meta['receivers'] = implode(', ', $this->additional['receivers'] ?? []);
        }
        if ($this->action === self::ACTION_SEND_PAYMENT_LINK) {
            $meta['client_name'] = $this->additional['name'];
            $meta['client_email'] = $this->additional['email'];
        }

        return $meta;
    }
}
