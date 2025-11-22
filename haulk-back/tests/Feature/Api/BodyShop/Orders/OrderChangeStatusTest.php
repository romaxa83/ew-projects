<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Orders\Order;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_change_status(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create(['status' => Order::STATUS_NEW]);
        $attributes = ['status' => Order::STATUS_IN_PROCESS];

        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'status' => Order::STATUS_IN_PROCESS,
                'id' => $order->id,
            ]
        );
    }

    public function test_change_status_validation_error(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create(['status' => Order::STATUS_NEW]);
        $attributes = ['status' => Order::STATUS_NEW];

        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $order->status = Order::STATUS_FINISHED;
        $order->status_changed_at = now()->addDays(-2);
        $order->save();

        $attributes = ['status' => Order::STATUS_IN_PROCESS];

        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_change_status_to_finished_with_inventory_with_diff_prices(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        $orderId = $response['data']['id'];
        $inventory->price_retail += 1;
        $inventory->save();

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'id' => $response['data']['types_of_work']['0']['id'],
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ],
                [
                    'name' => 'Name Test2',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];
        $this->postJson(route('body-shop.orders.update', $orderId), $formRequest)
            ->assertOk();

        $attributes = ['status' => Order::STATUS_FINISHED];

        $this->putJson(route('body-shop.orders.change-status', $orderId), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'status' => Order::STATUS_FINISHED,
                'id' => $orderId,
            ]
        );

        $this->assertDatabaseCount(Transaction::TABLE_NAME, 2);
    }
}
