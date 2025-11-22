<?php


namespace Api\Orders;


use App\Broadcasting\Events\Orders\NewOrderBroadcast;
use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\PushNotifications\PushNotificationTask;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\UserFactoryHelper;

class PushDriverTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_order_created_with_driver()
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id,
            ]
        );
        $orderData = $this->getRequiredFields() + $this->order_fields_create;
        $orderData['dispatcher_id'] = $dispatcher->id;
        $orderData['driver_id'] = $driver->id;
        $orderData['pickup_date'] = now()->addDays(2)->format('m/d/Y');

        Event::fake([
            ModelChanged::class,
            NewOrderBroadcast::class
        ]);

        // check if order visible
        $response = $this->postJson(
            route('orders.store'),
            $orderData
        )
            ->assertCreated();

        Event::assertDispatched(ModelChanged::class);
        Event::assertDispatched(NewOrderBroadcast::class);

        $orderId = $response->json('data.id');

        $this->getJson(route('orders.show', $orderId))
            ->assertOk();

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $dispatcher->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $dispatcher->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_new_order_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );
    }

    public function test_order_reassigned()
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id,
            ]
        );
        $orderData = $this->getRequiredFields() + $this->order_fields_create;
        $orderData['dispatcher_id'] = $dispatcher->id;
        $orderData['driver_id'] = $driver->id;
        $orderData['pickup_date'] = now()->addDays(2)->format('m/d/Y');

        // create order

        $response = $this->postJson(
            route('orders.store'),
            $orderData
        )
            ->assertCreated();

        $orderId = $response->json('data.id');

        // reassign driver and dispatcher

        $dispatcher2 = $this->dispatcherFactory();
        $driver2 = $this->driverFactory(
            [
                'owner_id' => $dispatcher2->id,
            ]
        );

        Event::fake([
            ModelChanged::class,
            OrderUpdateEvent::class,
            UpdateOrderBroadcast::class
        ]);

        $this->putJson(
            route('orders.assign-driver', $orderId),
            [
                'dispatcher_id' => $dispatcher2->id,
                'driver_id' => $driver2->id,
            ]
        )
            ->assertOk();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(OrderUpdateEvent::class, 1);
        Event::assertDispatched(UpdateOrderBroadcast::class, 1);

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'driver_new_order_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_new_order_once',
                'order_id' => $orderId,
                'user_id' => $driver2->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $driver2->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $driver2->id,
            ]
        );
    }

    public function test_order_updated()
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id,
            ]
        );
        $orderData = $this->getRequiredFields() + $this->order_fields_create;
        $orderData['dispatcher_id'] = $dispatcher->id;
        $orderData['driver_id'] = $driver->id;
        $orderData['pickup_date'] = now()->addDays(2)->format('m/d/Y');

        // create order
        $orderId = $this->postJson(
            route('orders.store'),
            $orderData
        )->assertCreated()
            ->json('data.id');
        // update order with new driver and dispatcher

        $dispatcher2 = $this->dispatcherFactory();
        $driver2 = $this->driverFactory(
            [
                'owner_id' => $dispatcher2->id,
            ]
        );

        $orderData['dispatcher_id'] = $dispatcher2->id;
        $orderData['driver_id'] = $driver2->id;

        $this->postJson(
            route('orders.update-order', $orderId),
            $orderData
        )
            ->assertOk();

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $dispatcher->id,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $dispatcher->id,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'driver_new_order_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseMissing(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $driver->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $dispatcher2->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $dispatcher2->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_new_order_once',
                'order_id' => $orderId,
                'user_id' => $driver2->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_24_once',
                'order_id' => $orderId,
                'user_id' => $driver2->id,
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_pickup_1_once',
                'order_id' => $orderId,
                'user_id' => $driver2->id,
            ]
        );
    }
}
