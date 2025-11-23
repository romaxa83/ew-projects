<?php

namespace WezomCms\Orders\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Events\CanceledOrder;
use WezomCms\Orders\Notifications\CancelOrderNotification;

/**
 * Class SendCanceledOrderNotification
 *
 * @package WezomCms\Orders\Listeners
 */
class SendCanceledOrderNotification implements ShouldQueue
{
    /**
     * Handle the event
     *
     * @param  CanceledOrder $canceledOrder
     */
    public function handle(CanceledOrder $canceledOrder)
    {
        Notification::send(
            Administrator::toNotifications(['orders.edit', 'orders.show'])->get(),
            new CancelOrderNotification($canceledOrder->order)
        );
    }
}
