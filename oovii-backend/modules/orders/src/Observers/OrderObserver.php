<?php

namespace WezomCms\Orders\Observers;

use WezomCms\Firebase\Events\FcmPush;
use WezomCms\Firebase\Models\Template;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Services\BonusService;

class OrderObserver
{
    /**
     * @param  Order  $order
     */
    public function updated(Order $order): void
    {
        if ($order->status_id && !$order->wasRecentlyCreated && $order->wasChanged('status_id')) {
            // Notification::send($order->user ?: $order->client, new OrderStatusChangedNotification($order));

            if ($order->user && $order->user->ref_id && $order->isStatus(OrderStatus::DONE)) {
                $this->addUserBonus($order);
            }

            if ($order->delivery && $driver = $order->delivery->makeDriver()) {
                $driver->handleOrderUpdate($order);
            }
        }
    }

    public function saved(Order $order): void
    {
        if ($order->status_id
            && $order->user
            && ($order->wasRecentlyCreated || $order->wasChanged('status_id'))
        ) {
            event(new FcmPush($order->user, Template::TYPE_ORDER_CHANGE_STATUS, $order));
        }
    }

    private function addUserBonus(Order $order): void
    {
        if (!$order->statusHistory->contains(fn(OrderStatus $status) => $status->isDone())) {
            $bonusService = resolve(BonusService::class);

            $bonusService->createReferralBonusHistory($order);
        }
    }
}
