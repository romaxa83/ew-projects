<?php


namespace Api\Payrolls;


use App\Broadcasting\Events\Payroll\PayrollCreateBroadcast;
use App\Broadcasting\Events\Payroll\PayrollDeleteBroadcast;
use App\Broadcasting\Events\Payroll\PayrollMarkIsPaidBroadcast;
use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class PayrollsTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    public function test_prepare_payroll(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $price1 = 100.5;
        $price2 = 1250;
        $price3 = 50;
        $price4 = 150;

        $dispatcher = $this->dispatcherFactory();

        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order1->id, $price1);

        $order2 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order2->id, $price2);

        Expense::factory()->create(
            [
                'order_id' => $order1->id,
                'price' => $price3
            ]
        );

        Expense::factory()->create(
            [
                'order_id' => $order2->id,
                'price' => $price4,
            ]
        );

        $this->postJson(
            route('payrolls.prepare'),
            [
                'driver_id' => $driver->id,
                'orders' => [
                    [
                        'id' => $order1->id,
                        'load_id' => $order1->load_id
                    ],
                    [
                        'id' => $order2->id,
                        'load_id' => $order2->load_id
                    ],
                ]
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.driver_id', $driver->id)
            ->assertJsonCount(2, 'data.orders')
            ->assertJsonPath('data.total', $price1 + $price2)
            ->assertJsonPath('data.subtotal', $price1 + $price2 - $price3 - $price4);
    }

    public function test_wrong_dates_on_payroll_create(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $price1 = 100.5;

        $dispatcher = $this->dispatcherFactory();

        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order1->id, $price1);

        $payrollData = [
            'driver_id' => $driver->id,
            'driver_rate' => 30.4,
            'orders' => [
                [
                    'id' => $order1->id,
                    'load_id' => $order1->load_id
                ]
            ],
            'total' => 100,
            'subtotal' => 200,
            'commission' => 300,
            'salary' => 400,
        ];

        Event::fake([PayrollCreateBroadcast::class]);

        $this->postJson(
            route('payrolls.store'),
            $payrollData + [
                'start' => now()->addDay()->format('m/d/Y'),
                'end' => now()->format('m/d/Y'),
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        Event::assertNotDispatched(PayrollCreateBroadcast::class);

        $this->postJson(
            route('payrolls.store'),
            $payrollData + [
                'start' => now()->format('m/d/Y'),
                'end' => now()->addDay()->format('m/d/Y'),
            ]
        )
            ->assertCreated();

        Event::assertDispatched(PayrollCreateBroadcast::class);
    }

    public function test_create_payroll(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $price1 = 100.5;
        $price2 = 1250;

        $dispatcher = $this->dispatcherFactory();

        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order1->id, $price1);

        $order2 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order2->id, $price2);

        Event::fake([PayrollCreateBroadcast::class]);

        $this->postJson(
            route('payrolls.store'),
            [
                'start' => now()->format('m/d/Y'),
                'end' => now()->addDay()->format('m/d/Y'),
                'driver_id' => $driver->id,
                'driver_rate' => 30.4,
                'orders' => [
                    [
                        'id' => $order1->id,
                        'load_id' => $order1->load_id
                    ],
                    [
                        'id' => $order2->id,
                        'load_id' => $order2->load_id
                    ],
                ],
                'total' => 100,
                'subtotal' => 200,
                'commission' => 300,
                'salary' => 400,
                'order_expenses' => [
                    [
                        'load_id' => '12312',
                        'type' => 'type 1',
                        'price' => 123,
                        'date' => now()->format('m/d/Y'),
                    ],
                    [
                        'load_id' => '32321',
                        'type' => 'type 2',
                        'price' => 321,
                        'date' => now()->format('m/d/Y'),
                    ],
                ],
                'expenses_before' => [
                    [
                        'type' => 'type 1',
                        'price' => 123,
                    ],
                    [
                        'type' => 'type 2',
                        'price' => 321,
                    ],
                ],
                'expenses_after' => [
                    [
                        'type' => 'type 1',
                        'price' => 123,
                    ],
                    [
                        'type' => 'type 2',
                        'price' => 321,
                    ],
                ],
                'bonuses' => [
                    [
                        'type' => 'type 1',
                        'price' => 123,
                    ],
                    [
                        'type' => 'type 2',
                        'price' => 321,
                    ],
                ],
            ]
        )
            ->assertCreated();

        Event::assertDispatched(PayrollCreateBroadcast::class);
    }

    public function test_mark_payroll_paid(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $price1 = 100.5;

        $dispatcher = $this->dispatcherFactory();

        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order1->id, $price1);

        Event::fake([
            PayrollCreateBroadcast::class,
            PayrollMarkIsPaidBroadcast::class
        ]);

        $response = $this->postJson(
            route('payrolls.store'),
            [
                'start' => now()->format('m/d/Y'),
                'end' => now()->addDay()->format('m/d/Y'),
                'driver_id' => $driver->id,
                'driver_rate' => 30.4,
                'orders' => [
                    [
                        'id' => $order1->id,
                        'load_id' => $order1->load_id
                    ],
                ],
                'total' => 100,
                'subtotal' => 200,
                'commission' => 300,
                'salary' => 400,
            ]
        )
            ->assertCreated();

        Event::assertDispatched(PayrollCreateBroadcast::class);

        $data = $response->json('data');

        $this->putJson(
            route('payrolls.mark-as-paid'),
            [
                'id' => [
                    $data['id'],
                ],
            ]
        )
            ->assertOk();

        Event::assertDispatched(PayrollMarkIsPaidBroadcast::class, 1);

        $this->getJson(route('payrolls.show', $data['id']))
            ->assertOk()
            ->assertJsonPath('data.is_paid', true);
    }

    public function test_delete_payroll(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $price1 = 100.5;

        $dispatcher = $this->dispatcherFactory();

        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order1->id, $price1);

        Event::fake([
            PayrollCreateBroadcast::class,
            PayrollDeleteBroadcast::class
        ]);

        $response = $this->postJson(
            route('payrolls.store'),
            [
                'start' => now()->format('m/d/Y'),
                'end' => now()->addDay()->format('m/d/Y'),
                'driver_id' => $driver->id,
                'driver_rate' => 30.4,
                'orders' => [
                    [
                        'id' => $order1->id,
                        'load_id' => $order1->load_id
                    ],
                ],
                'total' => 100,
                'subtotal' => 200,
                'commission' => 300,
                'salary' => 400,
            ]
        )
            ->assertCreated();

        Event::assertDispatched(PayrollCreateBroadcast::class);

        $data = $response->json('data');

        $this->deleteJson(
            route('payrolls.delete-many'),
            [
                'id' => [
                    $data['id'],
                ],
            ]
        )
            ->assertNoContent();

        Event::assertDispatched(PayrollDeleteBroadcast::class, 1);
    }

    public function test_order_deduct_from_driver(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $price1 = 100.5;
        $price2 = 1250;

        $dispatcher = $this->dispatcherFactory();

        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );

        $this->createOrderPayment($order1->id, $price1);

        $order2 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_DELIVERED,
            ]
        );
        $order2->deduct_from_driver = true;
        $order2->save();

        $this->createOrderPayment($order2->id, $price2);

        $this->postJson(
            route('payrolls.prepare'),
            [
                'driver_id' => $driver->id,
                'orders' => [
                    [
                        'id' => $order1->id,
                        'load_id' => $order1->load_id
                    ],
                    [
                        'id' => $order2->id,
                        'load_id' => $order2->load_id
                    ],
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.driver_id', $driver->id)
            ->assertJsonCount(2, 'data.orders')
            ->assertJsonPath('data.total', $price1 + $price2)
            ->assertJsonPath('data.subtotal', $price1 + $price2)
            ->assertJsonCount(1, 'data.expenses_after');
    }
}
