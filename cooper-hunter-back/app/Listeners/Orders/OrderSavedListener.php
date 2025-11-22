<?php

namespace App\Listeners\Orders;

use App\Enums\Orders\OrderSubscriptionActionEnum;
use App\Events\Orders\OrderSavedEvent;
use App\GraphQL\Subscriptions\BackOffice\Orders\OrderSubscription as BackOrderSubscription;
use App\GraphQL\Subscriptions\FrontOffice\Orders\OrderSubscription as FrontOrderSubscription;
use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use App\Services\Orders\OrderStatusService;

class OrderSavedListener
{

    public function __construct(private OrderStatusService $orderStatusService)
    {
    }

    public function handle(OrderSavedEvent $event): void
    {
        $order = $event->getOrder();

        if (!$order->wasRecentlyCreated && !$order->wasChanged()) {
            return;
        }

        $user = $event->getUser();

        if ($order->wasRecentlyCreated || $order->wasChanged('status')) {
            $this->orderStatusService->saveHistory($order->refresh(), $user);
        }

        if ($order->wasRecentlyCreated) {
            if ($user instanceof Technician) {
                Admin::all()
                    ->each(
                        fn(Admin $admin) => BackOrderSubscription::notify()
                            ->toUser($admin)
                            ->withContext(
                                [
                                    'id' => $order->id,
                                    'action' => OrderSubscriptionActionEnum::CREATED
                                ]
                            )
                            ->broadcast()
                    );
            } else {
                FrontOrderSubscription::notify()
                    ->toUser($order->technician)
                    ->withContext(
                        [
                            'id' => $order->id,
                            'action' => OrderSubscriptionActionEnum::CREATED
                        ]
                    )
                    ->broadcast();
            }
            return;
        }

        FrontOrderSubscription::notify()
            ->toUser($order->technician)
            ->withContext(
                [
                    'id' => $order->id,
                    'action' => OrderSubscriptionActionEnum::UPDATED
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
                            'action' => OrderSubscriptionActionEnum::UPDATED
                        ]
                    )
                    ->broadcast()
            );
    }
}
