<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Offers\TakenOfferBroadcast;
use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Event;

class TakeOrderTest extends OrderTestCase
{

    public function test_it_take_order_by_dispatcher()
    {
       $dispatcher = $this->loginAsCarrierDispatcher();

        $order = Order::factory()->create();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'load_id' => $order->load_id,
                'dispatcher_id' => null,
            ]
        );

        Event::fake([
            ModelChanged::class,
            OrderUpdateEvent::class,
            TakenOfferBroadcast::class
        ]);

        $this->putJson(route('orders.take', $order->id))
            ->assertOk();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(OrderUpdateEvent::class, 1);
        Event::assertDispatched(TakenOfferBroadcast::class, 1);

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'load_id' => $order->load_id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
            ]
        );
    }
}
