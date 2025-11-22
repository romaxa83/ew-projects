<?php


namespace Api\Orders\Mobile;


use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\InspectionMethodsHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class MobileStatusTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;
    use InspectionMethodsHelper;
    use OrderESSavingHelper;
    use ElasticsearchClear;

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

    public function test_order_category_payment_cop(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            self::VIN,
            self::AMOUNT,
            Payment::METHOD_CASH
        );

        $vehicle = $order->vehicles->first();

        $this->loginAsCarrierDriver($driver);

        $this->makeDocuments();

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.order_category', 'in_work');

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_PICKED_UP)
            ->assertJsonPath('data.order_category', 'in_work')
            ->assertJsonPath('data.payment.driver_payment_data_sent', false);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.order_category', 'in_work')
            ->assertJsonPath('data.payment.driver_payment_data_sent', false);

        $this->sendPayment($order->id, self::AMOUNT);

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.order_category', 'history')
            ->assertJsonPath('data.payment.driver_payment_data_sent', true);
    }

    public function test_order_category_payment_other(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order = $this->orderFactory(
            [
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'vin' => self::VIN,
            ]
        );

        Payment::factory()->create(
            [
                'order_id' => $order->id,
                'customer_payment_amount' => null,
                'customer_payment_method_id' => null,
                'customer_payment_location' => null,
                'total_carrier_amount' => self::AMOUNT,
                'broker_payment_amount' => self::AMOUNT,
                'broker_payment_method_id' => Payment::METHOD_ACH,
                'broker_payment_begins' => Order::LOCATION_PICKUP,
            ]
        );

        $vehicle = $order->vehicles->first();

        $this->loginAsCarrierDriver($driver);

        $this->makeDocuments();

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.order_category', 'in_work');

        $this->sendVin($order->id, $vehicle->id, self::VIN);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_PICKED_UP)
            ->assertJsonPath('data.order_category', 'in_work')
            ->assertJsonPath('data.payment.driver_payment_data_sent', false);

        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_DELIVERY);
        $this->sendSignature($order->id, self::INSPECTION_DELIVERY);

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.order_category', 'history')
            ->assertJsonPath('data.payment.driver_payment_data_sent', false);
    }
}
