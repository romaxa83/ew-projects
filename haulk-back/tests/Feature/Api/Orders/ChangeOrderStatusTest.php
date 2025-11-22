<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class ChangeOrderStatusTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    public function test_error_with_no_status_passed(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $this->putJson(route('orders.change-order-status', $order))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_fails_new_to_all_others(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $statusList = [
            Order::CALCULATED_STATUS_NEW,
            Order::CALCULATED_STATUS_ASSIGNED,
            Order::CALCULATED_STATUS_PICKED_UP,
            Order::CALCULATED_STATUS_DELIVERED,
        ];

        foreach ($statusList as $status) {
            $this->putJson(
                route('orders.change-order-status', $order),
                [
                    'status' => $status,
                ]
            )
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function test_assigned_to_new_success(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $this->assertTrue($order->isStatusAssigned());

        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_NEW,
            ]
        )
            ->assertStatus(Response::HTTP_OK);

        $order->refresh();

        $this->assertTrue($order->isStatusNew());
        $this->assertNull($order->driver_id);
        $this->assertNull($order->allowed_status_change);
    }

    public function test_assigned_to_picked_up_to_delivered(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'vin' => '1234567890abcdefg',
            ]
        );

        $this->createOrderPayment($order->id, 1234);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data.allowed_status_change');

        $this->assertTrue($order->isStatusAssigned());

        // set picked up
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_PICKED_UP,
                'pickup_date_actual' => now()->format('m/d/Y'),
            ]
        )
            ->assertStatus(Response::HTTP_OK);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.status', Order::STATUS_PICKED_UP)
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.vehicles.0.pickup_inspection.has_inspection', true)
            ->assertJsonPath('data.has_delivery_inspection', false)
            ->assertJsonPath('data.has_delivery_signature', false)
            ->assertJsonPath('data.vehicles.0.delivery_inspection.has_inspection', false)
            ->assertJsonCount(2, 'data.allowed_status_change');

        $order->refresh();

        $this->assertTrue($order->isStatusPickedUp());

        // set delivered
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_DELIVERED,
                'delivery_date_actual' => now()->format('m/d/Y'),
            ]
        )
            ->assertStatus(Response::HTTP_OK);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.vehicles.0.pickup_inspection.has_inspection', true)
            ->assertJsonPath('data.has_delivery_inspection', true)
            ->assertJsonPath('data.has_delivery_signature', true)
            ->assertJsonPath('data.vehicles.0.delivery_inspection.has_inspection', true)
            ->assertJsonCount(1, 'data.allowed_status_change');

        $order->refresh();

        $this->assertTrue($order->isStatusDelivered());
    }

    public function test_assigned_to_delivered_and_back(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'vin' => '1234567890abcdefg',
            ]
        );

        $this->createOrderPayment($order->id, 1234);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $this->assertTrue($order->isStatusAssigned());

        // set delivered
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_DELIVERED,
                'pickup_date_actual' => now()->format('m/d/Y'),
                'delivery_date_actual' => now()->format('m/d/Y'),
            ]
        )
            ->assertStatus(Response::HTTP_OK);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.vehicles.0.pickup_inspection.has_inspection', true)
            ->assertJsonPath('data.has_delivery_inspection', true)
            ->assertJsonPath('data.has_delivery_signature', true)
            ->assertJsonPath('data.vehicles.0.delivery_inspection.has_inspection', true);

        $order->refresh();

        $this->assertTrue($order->isStatusDelivered());

        // set picked up
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_PICKED_UP,
            ]
        )
            ->assertStatus(Response::HTTP_OK);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.status', Order::STATUS_PICKED_UP)
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.vehicles.0.pickup_inspection.has_inspection', true)
            ->assertJsonPath('data.has_delivery_inspection', false)
            ->assertJsonPath('data.has_delivery_signature', false)
            ->assertJsonPath('data.vehicles.0.delivery_inspection.has_inspection', false);

        $order->refresh();

        $this->assertTrue($order->isStatusPickedUp());

        // set assigned
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_ASSIGNED,
            ]
        )
            ->assertOk();

        $this->getJson(route('orders.show', $order))
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_NEW)
            ->assertJsonPath('data.has_pickup_inspection', false)
            ->assertJsonPath('data.has_pickup_signature', false)
            ->assertJsonPath('data.vehicles.0.pickup_inspection', null)
            ->assertJsonPath('data.has_delivery_inspection', false)
            ->assertJsonPath('data.has_delivery_signature', false)
            ->assertJsonPath('data.vehicles.0.delivery_inspection', null);

        $order->refresh();

        $this->assertTrue($order->isStatusAssigned());

        // set new
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_NEW,
            ]
        )
            ->assertOk();

        $order->refresh();

        $this->assertTrue($order->isStatusNew());
        $this->assertNull($order->driver_id);
    }

    public function test_from_billed_to_delivered(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'vin' => '1234567890abcdefg',
                'is_billed' => true,
            ]
        );

        $this->createOrderPayment($order->id, 1234);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $this->assertTrue($order->isStatusAssigned());

        // set delivered
        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_DELIVERED,
                'pickup_date_actual' => now()->format('m/d/Y'),
                'delivery_date_actual' => now()->format('m/d/Y'),
            ]
        )
            ->assertStatus(Response::HTTP_OK);

        $this->getJson(route('orders.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $order->refresh();

        $this->assertTrue($order->isStatusDelivered());
    }
}
