<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Offers\ReleaseOfferBroadcast;
use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class AssignDriverTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    public function test_assign_driver_to_order_can_other_dispatcher()
    {
        $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $dispatcherOther = $this->dispatcherFactory();
        $driverOther = $this->driverFactory(
            [
                'owner_id' => $dispatcherOther->id
            ]
        );

        $dispatcherNoDriver = $this->dispatcherFactory();

        // create order
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'seen_by_driver' => false,
            ]
        );

        Event::fake([
            ModelChanged::class,
            UpdateOrderBroadcast::class,
            OrderUpdateEvent::class,
            ReleaseOfferBroadcast::class
        ]);

        // try to assign driver not paired with dispatcher
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => $dispatcherNoDriver->id,
                'driver_id' => $driverOther->id,
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // try to assign correct dispatcher/driver pair
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => $dispatcherOther->id,
                'driver_id' => $driverOther->id,
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'dispatcher_id' => $dispatcherOther->id,
                'driver_id' => $driverOther->id,
                'seen_by_driver' => false,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'order_id' => $order->id,
                'user_id' => $driverOther->id,
                'type' => 'driver_new_order_once'
            ]
        );

        // try with empty driver
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => $dispatcherOther->id,
                'driver_id' => null,
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'dispatcher_id' => $dispatcherOther->id,
                'driver_id' => null,
                'seen_by_driver' => false,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'order_id' => $order->id,
                'user_id' => $driverOther->id,
                'type' => 'driver_new_order_once'
            ]
        );

        // try with both empty
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => null,
                'driver_id' => null,
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'dispatcher_id' => null,
                'driver_id' => null,
                'seen_by_driver' => false,
            ]
        );

        Event::assertDispatched(ModelChanged::class, 3);
        Event::assertDispatched(OrderUpdateEvent::class, 3);
        Event::assertDispatched(UpdateOrderBroadcast::class, 2);
        Event::assertDispatched(ReleaseOfferBroadcast::class, 1);
    }


    public function test_try_null_driver_on_picked_up_order()
    {
        $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        // create order
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_PICKED_UP,
                'seen_by_driver' => true,
            ]
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'seen_by_driver' => true,
            ]
        );

        // try to assign null driver
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => null,
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // try to assign null driver and null dispatcher
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => null,
                'driver_id' => null,
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // change status
        $order->status = Order::STATUS_NEW;
        $order->save();

        // try to assign null driver and null dispatcher
        $this->putJson(
            route('orders.assign-driver', $order),
            [
                'dispatcher_id' => null,
                'driver_id' => null,
            ]
        )
            ->assertOk();
    }
}
