<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Users\User;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class UpdateOrderTest extends OrderTestCase
{
    use OrderFactoryHelper;
    use UserFactoryHelper;

    public function test_it_can_update_dispatcher_or_driver_for_order_with_paid_status(): void
    {
        $order = Order::factory()
            ->deliveredStatus()
            ->has(
                Payment::factory()->paid(),
                'payment'
            )
            ->create();

        $this->loginAsCarrierDispatcher();

        $driver = User::factory()->driver()->create();
        $dispatcher = User::factory()->dispatcher()->create();

        $requireData = $this->getRequiredFields();

        Event::fake([
            ModelChanged::class,
            OrderUpdateEvent::class,
            UpdateOrderBroadcast::class
        ]);

        $this->postJson(
            route('orders.update-order', $order),
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ] + $requireData
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(OrderUpdateEvent::class, 1);
        Event::assertDispatched(UpdateOrderBroadcast::class, 1);
    }

    public function test_it_can_update_dispatcher_or_driver_for_order_with_billed_status(): void
    {
        $order = Order::factory(['is_billed' => true])
            ->deliveredStatus()
            ->has(
                Payment::factory()->customer(2000),
                'payment'
            )
            ->create();

        $this->loginAsCarrierDispatcher();

        $driver = User::factory()->driver()->create();
        $dispatcher = User::factory()->dispatcher()->create();

        $requireData = $this->getRequiredFields();

        $this->postJson(
            route('orders.update-order', $order),
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ] + $requireData
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'is_billed' => true,
            ]
        );
    }
}
