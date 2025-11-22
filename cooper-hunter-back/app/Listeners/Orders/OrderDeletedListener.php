<?php


namespace App\Listeners\Orders;


use App\Enums\Orders\OrderSubscriptionActionEnum;
use App\Events\Orders\OrderDeletedEvent;
use App\GraphQL\Subscriptions\BackOffice\Orders\OrderSubscription as BackOrderSubscription;
use App\GraphQL\Subscriptions\FrontOffice\Orders\OrderSubscription as FrontOrderSubscription;
use App\Models\Admins\Admin;

class OrderDeletedListener
{
    public function __construct()
    {
    }

    public function handle(OrderDeletedEvent $event): void
    {
        $order = $event->getOrder();

        FrontOrderSubscription::notify()
            ->toUser($order->technician)
            ->withContext(
                [
                    'id' => $order->id,
                    'action' => OrderSubscriptionActionEnum::DELETED
                ]
            )
            ->broadcast();

        Admin::all()
            ->each(
                fn(Admin $admin) => BackOrderSubscription::notify()
                    ->toUser($admin)
                    ->withContext(
                        [
                            'id' => $order->id,
                            'action' => OrderSubscriptionActionEnum::DELETED
                        ]
                    )
                    ->broadcast()
            );
    }
}
