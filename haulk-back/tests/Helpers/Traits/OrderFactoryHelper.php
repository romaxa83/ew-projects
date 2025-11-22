<?php

namespace Tests\Helpers\Traits;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use App\Models\Orders\Vehicle;
use App\Models\Users\User;
use Illuminate\Support\Arr;

trait OrderFactoryHelper
{
    use UserFactoryHelper;

    public function createOrderPayment(int $orderId, float $amount, int $paymentMethod = Payment::METHOD_USHIP): Payment
    {
        return Payment::factory()->create(
            [
                'order_id' => $orderId,
                'total_carrier_amount' => $amount,
                'customer_payment_amount' => $amount,
                'customer_payment_method_id' => $paymentMethod,
                'customer_payment_location' => Order::LOCATION_PICKUP,
            ]
        );
    }

    public function generateFakeOrdersWithMobileHistoryStatus(User $driver = null): array
    {
        $dispatcher = $this->dispatcherFactory();

        if (!$driver) {
            $driver = $this->driverFactory();
        }

        $orderFactory = Order::factory();

        $order = [
            'driver_id' => $driver->id,
            'dispatcher_id' => $dispatcher->id,
            'status' => Order::STATUS_DELIVERED,
            'has_delivery_inspection' => true,
            'has_delivery_signature' => true,
            'delivery_date_actual' => now()->getTimestamp(),
        ];

        $newestOrder = Order::factory()->create($order);

        $order['delivery_date_actual'] = now()->subHours(3)->getTimestamp();
        $bitOlderOrder = Order::factory()->create($order);

        $order['delivery_date_actual'] = now()->subDays(2)->getTimestamp();
        $muchOlderOrder = Order::factory()->create($order);

        $order['delivery_date_actual'] = now()->subDays(10)->getTimestamp();
        $oldestOrder = Order::factory()->create($order);

        return [
            'newest' => $newestOrder,
            'bitOlderOrder' => $bitOlderOrder,
            'muchOlderOrder' => $muchOlderOrder,
            'oldestOrder' => $oldestOrder,
        ];
    }

    protected function orderFactory(array $attributes = []): Order
    {
        $order = Order::factory()
            ->create(
                Arr::only(
                    $attributes,
                    [
                        'status',
                        'load_id',
                        'driver_id',
                        'inspection_type',
                        'owner_id',
                        'dispatcher_id',
                        'is_billed',
                        'shipper_contact',
                        'delivery_contact',
                        'pickup_contact',
                        'has_pickup_inspection',
                        'has_pickup_signature',
                        'has_delivery_inspection',
                        'has_delivery_signature',
                        'seen_by_driver',
                        'need_review',
                        'has_review',
                        'pickup_customer_refused_to_sign',
                        'pickup_customer_not_available',
                        'delivery_customer_refused_to_sign',
                        'delivery_customer_not_available',
                    ]
                )
            );

        Vehicle::factory()->create(
            ['order_id' => $order->id] + Arr::only(
                $attributes,
                [
                    'vin',
                    'make',
                    'model',
                    'type_id',
                    'pickup_inspection_id',
                    'delivery_inspection_id',
                ]
            )
        );

        return $order;
    }

    /**
     * @return Order[]
     */
    protected function generateFakeOrdersWithAllStatuses(): array
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory();

        $orderBilled = Order::factory()->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true
            ]
        );

        $orderPaid = Order::factory()->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => true
            ]
        );

        $orderNew = Order::factory()->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => null,
            ]
        );

        $orderAssigned = Order::factory()->create(
            [
                'status' => Order::STATUS_NEW,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
            ]
        );

        $orderPickedUp = Order::factory()->create(
            [
                'status' => Order::STATUS_PICKED_UP,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
            ]
        );

        $orderDelivered = Order::factory()->create(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => false
            ]
        );

        $orderDeleted = Order::factory()->create(
            [
                'deleted_at' => now()
            ]
        );

        return [
            Order::CALCULATED_STATUS_BILLED => $orderBilled,
            Order::CALCULATED_STATUS_PAID => $orderPaid,
            Order::CALCULATED_STATUS_NEW => $orderNew,
            Order::CALCULATED_STATUS_ASSIGNED => $orderAssigned,
            Order::CALCULATED_STATUS_PICKED_UP => $orderPickedUp,
            Order::CALCULATED_STATUS_DELIVERED => $orderDelivered,
            Order::CALCULATED_STATUS_DELETED => $orderDeleted,
        ];
    }

    public function setPaidAt(Order $order, ?int $paidAt = null)
    {
        if (!$order->payment) {
            Payment::factory()->create(['order_id' => $order->id]);

            $order->refresh();
        }

        $paidAt = $paidAt ?: time();

        if ($order->payment->customer_payment_amount) {
            PaymentStage::factory()->create(
                [
                    'order_id' => $order->id,
                    'payer' => Payment::PAYER_CUSTOMER,
                    'amount' => $order->payment->customer_payment_amount,
                    'payment_date' => $paidAt
                ]
            );
        }

        if ($order->payment->broker_payment_amount) {
            PaymentStage::factory()->create(
                [
                    'order_id' => $order->id,
                    'payer' => Payment::PAYER_BROKER,
                    'amount' => $order->payment->broker_payment_amount,
                    'payment_date' => $paidAt
                ]
            );
        }
    }

    public function generateFakeOrderWithAllData()
    {
        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();
        $order = Order::factory()->create([
            'driver_id' => $driver->id,
            'dispatcher_id' => $dispatcher->id,
            'pickup_contact' => [
                'state' => 'LA',
                'zip' => 22560,
            ],
            'pickup_date' => now()->addDay()->getTimestamp(),
            'pickup_time' => [
                'from' => '12:45 AM',
                'to' => '1:45 PM',
            ],
            'pickup_date_actual' => now()->getTimestamp(),
            'delivery_contact' => [
                'state' => 'HA',
                'zip' => 41847,
            ],
            'delivery_date' => now()->addDay()->getTimestamp(),
            'delivery_date_actual' => now()->addDays(10)->getTimestamp(),
            'shipper_contact' => [
                'name' => 'Shipper name',
            ],
        ]);
        Payment::factory()->create(
            [
                'order_id' => $order->id,
                'total_carrier_amount' => 2000,
                'customer_payment_amount' => 3000,
                'customer_payment_method_id' => Payment::METHOD_CASHAPP,
                'customer_payment_location' => Order::LOCATION_PICKUP,
                'broker_payment_method_id' => Payment::METHOD_VENMO,
                'broker_payment_amount' => '200',
                'broker_payment_days' => 3,
                'broker_fee_method_id' => Payment::METHOD_PAYPAL,
                'broker_fee_amount' => 100,
                'broker_fee_days' => 2,
                'driver_payment_method_id' => Payment::METHOD_CASHAPP,
                'driver_payment_amount' => 500,
            ]
        );

        Vehicle::factory()->create([
            'order_id' => $order->id,
            'make' => 'Audi',
            'model' => 'A3',
            'year' => 2021,
            'inop' => true,
        ]);

        Vehicle::factory()->create([
            'order_id' => $order->id,
            'make' => 'Audi',
            'model' => 'A2',
            'year' => 2015,
            'enclosed' => true,
        ]);
    }
}
