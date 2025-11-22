<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Orders\NewOrderBroadcast;
use App\Events\ModelChanged;
use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class DuplicateOrderTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    public function test_it_duplicate_created(): void
    {
        $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();

        Event::fake([
            ModelChanged::class,
            NewOrderBroadcast::class
        ]);

        $response = $this
            ->postJson(
                route('orders.store'),
                $this->getRequiredFields()
                + $this->order_fields_create
                + [
                    'dispatcher_id' => $dispatcher->id,
                ]
            )
            ->assertCreated();

        $order = $response->json('data');

        $response = $this->getJson(route('orders.duplicate-order', $order['id']))
            ->assertCreated();

        Event::assertDispatched(ModelChanged::class, 2);
        Event::assertDispatched(NewOrderBroadcast::class, 2);

        $duplicate = $response->json('data');

        $this->assertDatabaseHas(
            'orders',
            [
                'id' => $duplicate['id'],
                'load_id' => $order['load_id'] . ' duplicate',
            ]
        );

        // check order and duplicate id's are different
        $this->assertNotEquals($order['id'], $duplicate['id']);

        // check if payment data are same but id's are not
        $this->assertSame($order['payment']['terms'], $duplicate['payment']['terms']);
        $this->assertNotEquals($order['payment']['id'], $duplicate['payment']['id']);

        // check vehicle data equals but id's are not
        $this->assertSame($order['vehicles'][0]['vin'], $duplicate['vehicles'][0]['vin']);
        $this->assertSame($order['vehicles'][0]['make'], $duplicate['vehicles'][0]['make']);
        $this->assertNotEquals($order['vehicles'][0]['id'], $duplicate['vehicles'][0]['id']);
    }

    public function test_it_duplicate_assigned_order_success(): void
    {
        $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->createOrderPayment($order->id, 1234);

        Event::fake([
            ModelChanged::class,
            NewOrderBroadcast::class
        ]);

        $orderId = $this->getJson(route('orders.duplicate-order', $order))
            ->assertCreated()
            ->json('data.id');

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(NewOrderBroadcast::class, 1);

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'user_id' => $driver->id,
                'order_id' => $orderId,
                'type' => 'driver_new_order_once'
            ]
        );
    }

    public function test_it_duplicate_new_order_success(): void
    {
        $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => null,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->createOrderPayment($order->id, 1234);

        $this->getJson(route('orders.duplicate-order', $order))
            ->assertCreated();
    }

    public function test_cant_duplicate_order_with_status_offer(): void
    {
        $this->loginAsCarrierDispatcher();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => null,
                'dispatcher_id' => null,
            ]
        );

        $this->createOrderPayment($order->id, 1234);

        $response = $this->getJson(route('orders.duplicate-order', $order))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $errors = $response->json('errors');

        $this->assertEquals('This order can not be duplicated.', array_shift($errors)['title']);
    }

    public function test_it_duplicate_with_tags(): void
    {
        $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();

        Event::fake([
            ModelChanged::class,
            NewOrderBroadcast::class
        ]);

        $tag1 = Tag::factory()->create();
        $tags = [
            'tags' => [$tag1->id],
        ];

        $response = $this
            ->postJson(
                route('orders.store'),
                $this->getRequiredFields()
                + $this->order_fields_create
                + [
                    'dispatcher_id' => $dispatcher->id,
                ]
                + $tags
            )
            ->assertCreated();

        $order = $response->json('data');

        $response = $this->getJson(route('orders.duplicate-order', $order['id']))
            ->assertCreated();

        $duplicate = $response->json('data');

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag1->id,
                'taggable_id' => $duplicate['id'],
                'taggable_type' => Order::class,
            ]
        );
    }
}
