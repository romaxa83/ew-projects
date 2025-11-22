<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;

class OrderMobileIndexTest extends OrderTestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    public function test_it_show_result_for_empty_status(): void
    {
        $this->loginAsCarrierDriver();

        $this->generateFakeOrdersWithMobileHistoryStatus($this->authenticatedUser);
        $this->makeDocuments();

        $response = $this->getJson(route('order-mobile.index'))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(4, $orders);
    }

    public function test_in_work_tab_order_field(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        // new order without pickup date
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => true,
            ]
        );
        $this->makeDocuments();
        $this->getJson(route('order-mobile.index'))
            ->assertOk()
            ->assertJsonPath('data.0.order_category', 'in_work');

        // new order with today pickup date
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => true,
            ]
        );

        $order->pickup_date = now()->timestamp;
        $order->save();
        $this->makeDocuments();

        $this->getJson(route('order-mobile.index'))
            ->assertOk()
            ->assertJsonPath('data.0.order_category', 'in_work');
    }

    public function test_plan_tab_order_field(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        // new order with tomorrow pickup date
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => true,
            ]
        );

        $order->pickup_date = now()->addDays(2)->timestamp;
        $order->save();
        $this->makeDocuments();

        $this->getJson(route('order-mobile.index'))
            ->assertOk()
            ->assertJsonPath('data.0.order_category', 'plan');
    }

    public function test_in_work_tab_order_list(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        // new order without pickup date
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => true,
            ]
        );
        $this->makeDocuments();

        $orderStatus = 'in_work';

        $this->getJson(
            route(
                'order-mobile.index',
                [
                    'status' => $orderStatus,
                ]
            )
        )
            ->assertOk()
            ->assertJsonPath('data.0.order_category', $orderStatus);

        // new order with today pickup date
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => true,
            ]
        );

        $order->pickup_date = now()->timestamp;
        $order->save();
        $this->makeDocuments();

        $this->getJson(
            route(
                'order-mobile.index',
                [
                    'status' => $orderStatus,
                ]
            )
        )
            ->assertOk()
            ->assertJsonPath('data.0.order_category', $orderStatus);
    }

    public function test_plan_tab_order_list(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        // new order with tomorrow pickup date
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => true,
            ]
        );

        $order->pickup_date = now()->addDays(2)->timestamp;
        $order->save();
        $this->makeDocuments();

        $orderStatus = 'plan';

        $this->getJson(
            route(
                'order-mobile.index',
                [
                    'status' => $orderStatus
                ]
            )
        )
            ->assertOk()
            ->assertJsonPath('data.0.order_category', $orderStatus);

        $order->pickup_date = null;
        $order->save();
        $this->makeDocuments();

        $this->getJson(
            route(
                'order-mobile.index',
                [
                    'status' => $orderStatus
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
