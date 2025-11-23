<?php

namespace WezomCms\Orders\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Events\AutoPayedOrder;
use WezomCms\Orders\Notifications\AutoPayedOrderNotification;

/**
 * Class SendAutoPayedOrderNotification
 *
 * @package WezomCms\Orders\Listeners
 */
class SendAutoPayedOrderNotification implements ShouldQueue
{
    /**
     * Handle the event
     *
     * @param  AutoPayedOrder  $autoPayedOrder
     */
    public function handle(AutoPayedOrder $autoPayedOrder)
    {
        Notification::send(
            Administrator::toNotifications(['orders.edit', 'orders.show'])->get(),
            new AutoPayedOrderNotification($autoPayedOrder->order)
        );
    }
}
