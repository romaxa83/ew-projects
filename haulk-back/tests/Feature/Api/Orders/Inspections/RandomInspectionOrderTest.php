<?php

namespace Api\Orders\Inspections;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\InspectionMethodsHelper;
use Tests\TestCase;

;

class RandomInspectionOrderTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;
    use InspectionMethodsHelper;

    private const VIN = 'abcdefg1234567890';
    private const AMOUNT = 123;
    private const USHIP = '123654789';

    private const INSPECTION_PICKUP = 1;
    private const INSPECTION_DELIVERY = 2;

    private array $inspections = [
        self::INSPECTION_PICKUP => 'pickup',
        self::INSPECTION_DELIVERY => 'delivery',
    ];

    private function createOrder(int $dispatcherId, int $driverId, string $vin, int $amount, int $paymentMethod): Order
    {
        $order = $this->orderFactory(
            [
                'dispatcher_id' => $dispatcherId,
                'driver_id' => $driverId,
                'vin' => $vin,
            ]
        );

        $this->createOrderPayment($order->id, $amount, $paymentMethod);

        return $order;
    }

    public function test_pickup_cop_delivery(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);
        $this->sendPayment($order->id, self::AMOUNT);

        $order->refresh();

        $this->assertEquals(Order::STATUS_PICKED_UP, $order->status);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_cod_pickup_delivery(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendPayment($order->id, self::AMOUNT);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $order->refresh();

        $this->assertEquals(Order::STATUS_PICKED_UP, $order->status);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_delivery_cod_pickup(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendPayment($order->id, self::AMOUNT);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_delivery_pickup_cod(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $order->refresh();

        // payment doesn't affect order status
        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);

        $this->sendPayment($order->id, self::AMOUNT);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_pickup_delivery_cop(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $order->refresh();

        // payment doesn't affect order status
        $this->assertEquals(Order::STATUS_PICKED_UP, $order->status);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        // payment doesn't affect order status
        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);

        $this->sendPayment($order->id, self::AMOUNT);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_cop_uship_delivery_pickup(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendPaymentUship($order->id, self::USHIP);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertEquals(Order::MOBILE_TAB_HISTORY, $order->getCategoryForMobileApp());
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_cop_uship_pickup_delivery(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendPaymentUship($order->id, self::USHIP);

        $order->refresh();

        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $order->refresh();

        $this->assertEquals(Order::STATUS_PICKED_UP, $order->status);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertEquals(Order::MOBILE_TAB_HISTORY, $order->getCategoryForMobileApp());
        $this->assertNotNull($order->getCategoryForMobileApp());
    }

    public function test_manual_picked_up_then_delivered_inspection(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->loginAsCarrierDispatcher($dispatcher);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $this->assertTrue($order->isStatusAssigned());

        $this->putJson(
            route('orders.change-order-status', $order),
            [
                'status' => Order::CALCULATED_STATUS_PICKED_UP,
                'pickup_date_actual' => Carbon::now()->format('m/d/Y')
            ]
        )
            ->assertOk();

        $order->refresh();

        $this->assertTrue($order->isStatusPickedUp());

        $vehicle = $order->vehicles->first();

        $this->loginAsCarrierDriver($driver);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);
        $this->sendPayment($order->id, self::AMOUNT);

        $order->refresh();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertEquals(Order::MOBILE_TAB_HISTORY, $order->getCategoryForMobileApp());
    }
}
