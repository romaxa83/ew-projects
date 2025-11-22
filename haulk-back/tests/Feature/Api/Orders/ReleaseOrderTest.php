<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Offers\ReleaseOfferBroadcast;
use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Event;

class ReleaseOrderTest extends OrderTestCase
{

    public function test_it_release_dispatcher_from_order()
    {
        $this->loginAsCarrierDispatcher();

        $order = Order::factory()->create(['dispatcher_id' => $this->getAuthenticatedUser()->id]);

        Event::fake([
            ModelChanged::class,
            OrderUpdateEvent::class,
            ReleaseOfferBroadcast::class
        ]);

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'load_id' => $order->load_id,
                'dispatcher_id' => $order->dispatcher_id,
            ]
        );

        $this->putJson(route('orders.release', $order->id))
            ->assertOk();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(OrderUpdateEvent::class, 1);
        Event::assertDispatched(ReleaseOfferBroadcast::class, 1);

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'load_id' => $order->load_id,
                'dispatcher_id' => null,
                'status' => Order::STATUS_NEW,
            ]
        );
    }
}
