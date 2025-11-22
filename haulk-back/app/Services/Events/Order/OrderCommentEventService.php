<?php


namespace App\Services\Events\Order;


use App\Events\ModelChanged;
use App\Models\Orders\Order;
use App\Models\Orders\OrderComment;
use App\Notifications\Alerts\AlertNotification;
use App\Services\Events\EventService;
use App\Services\Histories\HistoryHandler;
use Lang;
use TypeError;

class OrderCommentEventService extends EventService
{
    private const HISTORY_MESSAGE_NEW_ORDER_COMMENT = 'history.store_order_comment';
    private const HISTORY_MESSAGE_DELETE_ORDER_COMMENT = 'history.delete_order_comment';

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
            'role' => $this->user->roles->first()->name,
            'email' => $this->user->email,
            'load_id' => $this->order->load_id,
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

    public function broadcast(): OrderCommentEventService
    {
        if ($this->order->user) {
            $this->order->user->notify(
                new AlertNotification(
                    $this->order->user->getCompanyId(),
                    self::HISTORY_MESSAGE_NEW_ORDER_COMMENT,
                    AlertNotification::TARGET_TYPE_ORDER_COMMENT,
                    ['order_id' => $this->order->id,],
                    $this->getHistoryMeta()
                )
            );
        }

        return $this;
    }
}
