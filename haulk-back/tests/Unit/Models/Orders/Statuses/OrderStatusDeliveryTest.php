<?php

namespace Tests\Unit\Models\Orders\Statuses;

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderStatusDeliveryTest extends TestCase
{
    use DatabaseTransactions;

    use OrderFactoryHelper;

    public function test_order_has_status_delivery_without_driver()
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory();

        $orderFactory = Order::factory();

        /** @var Order $orderDelivered */
        $orderDelivered = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => false,
            ]
        );

        $this->assertTrue($orderDelivered->isStatusDelivered());

        $orderDelivered->driver_id = null;

        $this->assertTrue($orderDelivered->isStatusDelivered());
    }
}
