<?php

namespace Tests\Unit\Listeners\Orders;

use App\Broadcasting\Events\Offers\NewOfferBroadcast;
use App\Models\Orders\Order;
use App\Services\Events\EventService;
use Event;
use Tests\TestCase;

class OrderCreateListenerTest extends TestCase {

    public function test_it_has_new_offer_broadcast()
    {
        Event::fake();

        $user = $this->loginAsCarrierSuperAdmin();

        $order = new Order();
        $order->id = 1;
        $order->carrier_id = 1;
        $order->status = Order::STATUS_NEW;

        EventService::order($order)->user($user)->create()->broadcast();

        Event::assertDispatched(NewOfferBroadcast::class);
    }
}
