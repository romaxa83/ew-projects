<?php

namespace WezomCms\Orders\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Notification;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Events\CreatedOrders;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Notifications\CreatedOrderNotification;

/**
 * Class SendCreatedOrdersNotification
 *
 * @package WezomCms\Orders\Listeners
 */
class SendCreatedOrdersNotification implements ShouldQueue
{
    /**
     * Handle the event
     *
     * @param  CreatedOrders  $createdOrders
     */
    public function handle(CreatedOrders $createdOrders)
    {
        foreach ($createdOrders->orders as $order) {
            Notification::send(
                $this->getAdministrators($order),
                new CreatedOrderNotification($order)
            );
        }

        /*try {
            $user = $createdOrders->orders->first()->user;

            Notification::send(
                $user,
                new UserCreatedOrdersNotification($createdOrders->orders)
            );
        } catch (Exception $e) {
            logger($e->getMessage());
        }*/
    }

    private function getAdministrators(Order $order): EloquentCollection
    {
        return Administrator::query()
            ->toNotifications(['orders.edit'])
            ->get()
            ->filter(function (Administrator $admin) use ($order) {
                return $admin->onlyProvider()
                    ? $order->provider->admin_id === $admin->id
                    : true;
            });
    }
}
