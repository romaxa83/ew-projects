<?php

namespace Tests\Feature\Api\Orders\Updating;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;

class OrderNewUpdateDriverTest extends OrderTestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_into_new_order_with_billed_payment_status_and_without_driver_can_assigned_new_driver()
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory();

        $orderFactory = Order::factory();

        /** @var Order $orderNew */
        $orderNew = $orderFactory
            ->has(
                Vehicle::factory()->count(2),
                'vehicles'
            )
            ->create(
                [
                    'status' => Order::STATUS_NEW,
                    'inspection_type' => Order::INSPECTION_TYPE_HARD,
                    'dispatcher_id' => $dispatcher->id,
                    'is_billed' => true,
                    'driver_id' => null,
                ]
            );

        Payment::factory()->create(['order_id' => $orderNew->id]);

        $this->loginAsCarrierDispatcher();

        $this->postJson(
            route('orders.update-order', $orderNew),
            [
                'vehicles' => $orderNew->vehicles->toArray(),
                'driver_id' => $driver->id,

                'load_id' => $orderNew->load_id,
                'dispatcher_id' => $orderNew->dispatcher_id,
                'inspection_type' => Order::INSPECTION_TYPE_HARD,

                'pickup_contact' => $this->getPickupContactData(),
                'delivery_contact' => $this->getDeliveryContactData(),
                'shipper_contact' => $this->getShipperContactData(),

                'payment' => $this->getPaymentData(),
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $orderNew->id,
                'driver_id' => $driver->id,
            ]
        );
    }
}
