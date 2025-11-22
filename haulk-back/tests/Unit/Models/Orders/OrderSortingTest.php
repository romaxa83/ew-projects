<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderSortingTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_orders_have_correct_sorting_order()
    {
        $this->markTestSkipped();

        $ordersWithStatuses = $this->generateFakeOrdersWithAllStatuses();

        $orders = Order::query()
            ->select(Order::TABLE_NAME . '.*')
            ->joinPayment()
            ->orderByStatus()
            ->orderByValuableDate()
            ->get();

        $this->assertEquals($orders->shift()->id, $ordersWithStatuses[Order::CALCULATED_STATUS_NEW]->id);
        $this->assertEquals($orders->shift()->id, $ordersWithStatuses[Order::CALCULATED_STATUS_ASSIGNED]->id);
        $this->assertEquals($orders->shift()->id, $ordersWithStatuses[Order::CALCULATED_STATUS_PICKED_UP]->id);
        $this->assertEquals($orders->shift()->id, $ordersWithStatuses[Order::CALCULATED_STATUS_DELIVERED]->id);
        $this->assertEquals($orders->shift()->id, $ordersWithStatuses[Order::CALCULATED_STATUS_BILLED]->id);
        $this->assertEquals($orders->shift()->id, $ordersWithStatuses[Order::CALCULATED_STATUS_PAID]->id);
    }

    public function test_orders_have_sorting_by_calculated_statues_and_valuable_date()
    {
        $this->markTestSkipped();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory();

        $orderFactory = Order::factory();

        /** @var Order $orderNew1 */
        $orderNew1 = $orderFactory->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => null,
                'pickup_date' => now()->subDays(3)->getTimestamp(),
            ]
        );

        /** @var Order $orderNew2 */
        $orderNew2 = $orderFactory->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => null,
                'pickup_date' => now()->subDays(1)->getTimestamp(),
            ]
        );

        /** @var Order $orderNew3 */
        $orderNew3 = $orderFactory->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => null,
                'created_at' => now()->subDays(2),
            ]
        );

        /** @var Order $orderAssigned1 */
        $orderAssigned1 = $orderFactory->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'pickup_date' => now()->subDays(1)->getTimestamp(),
            ]
        );

        /** @var Order $orderAssigned2 */
        $orderAssigned2 = $orderFactory->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'pickup_date' => now()->subDays(3)->getTimestamp(),
            ]
        );

        /** @var Order $orderAssigned3 */
        $orderAssigned3 = $orderFactory->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'created_at' => now()->subDays(2),
            ]
        );

        $orders = Order::query()
            ->select(Order::TABLE_NAME . '.*')
            ->joinPayment()
            ->orderByStatus()
            ->orderByValuableDate()
            ->get();

        $ids = $orders->map->id->toArray();

        $this->assertEquals(
            [
                $orderNew1->id,
                $orderNew3->id,
                $orderNew2->id,
                $orderAssigned2->id,
                $orderAssigned3->id,
                $orderAssigned1->id,
            ],
            $ids
        );
    }

    public function test_orders_have_sorting_by_calculated_statues_and_valuable_date2()
    {
        $this->markTestSkipped();

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory();

        $orderFactory = Order::factory();

        /** @var Order $orderBilled1 */
        $orderBilled1 = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true,
                'is_paid' => false,
            ]
        );

        Payment::factory()->create(
            [
                'invoice_issue_date' => now()->subDays(1)->getTimestamp(),
                'order_id' => $orderBilled1->id,
            ]
        );

        /** @var Order $orderBilled2 */
        $orderBilled2 = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true,
                'is_paid' => false,
            ]
        );

        Payment::factory()->create(
            [
                'invoice_issue_date' => now()->subDays(3)->getTimestamp(),
                'order_id' => $orderBilled2->id,
            ]
        );

        /** @var Order $orderBilled3 */
        $orderBilled3 = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true,
                'is_paid' => false,
            ]
        );

        Payment::factory()->create(
            [
                'invoice_issue_date' => now()->subDays(2)->getTimestamp(),
                'order_id' => $orderBilled3->id,
            ]
        );

        /** @var Order $orderPaid1 */
        $orderPaid1 = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true,
                'is_paid' => true,
            ]
        );

        Payment::factory()->create(
            [
                'receipt_date' => now()->subDays(1)->getTimestamp(),
                'order_id' => $orderPaid1->id,
            ]
        );

        /** @var Order $orderPaid2 */
        $orderPaid2 = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true,
                'is_paid' => true,
            ]
        );

        Payment::factory()->create(
            [
                'receipt_date' => now()->subDays(2)->getTimestamp(),
                'order_id' => $orderPaid2->id,
            ]
        );

        /** @var Order $orderPaid3 */
        $orderPaid3 = $orderFactory->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true,
                'is_paid' => true,
            ]
        );

        Payment::factory()->create(
            [
                'receipt_date' => now()->subDays(3)->getTimestamp(),
                'order_id' => $orderPaid3->id,
            ]
        );

        $orders = Order::query()
            ->select(Order::TABLE_NAME . '.*')
            ->joinPayment()
            ->orderByStatus()
            ->orderByValuableDate()
            ->get();

        $ids = $orders->map->id->toArray();

        $this->assertEquals(
            [
                $orderBilled2->id,
                $orderBilled3->id,
                $orderBilled1->id,
                $orderPaid3->id,
                $orderPaid2->id,
                $orderPaid1->id,
            ],
            $ids
        );
    }
}
