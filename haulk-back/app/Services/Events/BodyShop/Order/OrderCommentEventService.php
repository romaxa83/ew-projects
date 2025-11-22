<?php

namespace App\Services\Events\BodyShop\Order;

use App\Events\ModelChanged;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use App\Services\Events\EventService;
use App\Services\Histories\HistoryHandler;
use Lang;
use TypeError;

class OrderCommentEventService extends EventService
{
    private const HISTORY_MESSAGE_NEW_ORDER_COMMENT = 'history.bs.store_order_comment';
    private const HISTORY_MESSAGE_DELETE_ORDER_COMMENT = 'history.bs.delete_order_comment';

    private Order $order;

    private ?OrderComment $comment;

    private Order $orderForHandler;

    private ?HistoryHandler $historyHandler = null;

    public function __construct(Order $order, ?OrderComment $comment = null)
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

        $this->comment = $comment;
    }

    private function refreshObject(): void
    {
        $this->order->refresh();

        if ($this->historyHandler === null) {
            return;
        }

        $this->historyHandler->setDirty($this->order);
    }

    private function getHistoryMeta(): array
    {
        return [
            'full_name' => $this->user->full_name,
            'role' => $this->user->getRoleName(),
            'email' => $this->user->email,
            'order_number' => $this->order->order_number,
        ];
    }

    public function create(): OrderCommentEventService
    {
        $this->refreshObject();

        event(
            new ModelChanged(
                $this->order,
                self::HISTORY_MESSAGE_NEW_ORDER_COMMENT,
                $this->getHistoryMeta(),
                null,
                null,
                $this->historyHandler
            )
        );

        return $this;
    }

    public function delete(): OrderCommentEventService
    {
        $this->refreshObject();

        event(
            new ModelChanged(
                $this->order,
                self::HISTORY_MESSAGE_DELETE_ORDER_COMMENT,
                [
                    'full_name_1' => $this->user->full_name,
                    'full_name_2' => $this->comment->user->full_name,
                ],
                null,
                null,
                $this->historyHandler
            )
        );

        return $this;
    }
}
