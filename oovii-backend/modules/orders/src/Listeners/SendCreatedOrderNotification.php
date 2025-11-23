<?php

namespace WezomCms\Orders\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Events\CreatedOrder;
use WezomCms\Orders\Notifications\CreatedOrderNotification;
use WezomCms\Orders\Notifications\UserCreatedOrderNotification;

/**
 * Class SendCreatedOrderNotification
 *
 * @package WezomCms\Orders\Listeners
 */
class SendCreatedOrderNotification implements ShouldQueue
{
    /**
     * Handle the event
     *
     * @param  CreatedOrder  $createdOrder
     */
    public function handle(CreatedOrder $createdOrder)
    {
        Notification::send(
            Administrator::toNotifications(['orders.edit', 'orders.show'])->get(),
            new CreatedOrderNotification($createdOrder->order)
        );

        try {
            Notification::send(
                $createdOrder->order->user ?: $createdOrder->order->client,
                new UserCreatedOrderNotification($createdOrder->order)
            );
        } catch (Exception $e) {
            logger($e->getMessage());
        }
    }
}
