<?php


namespace Api\Orders\Inspections;


use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Services\Orders\OrderStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\InspectionMethodsHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class RestoreVehicleVinWhileRollbackTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;
    use UserFactoryHelper;
    use InspectionMethodsHelper;

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

    public function test_restore_vehicle_vin_while_rollback(): void
    {
        $inspectedVin = 'abcdefg1234567890';
        $partialVin = '567890';
        $price = 1234;

        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $user = $this->loginAsCarrierDriver($driver);

        $order = $this->createOrder(
            $dispatcher->id,
            $driver->id,
            $partialVin,
            $price,
            Payment::METHOD_CASH
        );

        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        $vehicle = $order->vehicles->first();

        $this->sendVin($order->id, $vehicle->id, $inspectedVin);
        $this->sendDamage($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendExterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendInterior($order->id, $vehicle->id, self::INSPECTION_PICKUP);
        $this->sendSignature($order->id, self::INSPECTION_PICKUP);
        $this->sendPayment($order->id, $price);

        $order->refresh();

        $this->assertEquals(Order::STATUS_PICKED_UP, $order->status);

        $statusService = resolve(OrderStatusService::class);

        $statusService->setUser($user);

        $order = $statusService->changeStatus($order, Order::CALCULATED_STATUS_ASSIGNED, []);

        $this->assertSame($order->vehicles[0]->vin, $partialVin);
    }
}
