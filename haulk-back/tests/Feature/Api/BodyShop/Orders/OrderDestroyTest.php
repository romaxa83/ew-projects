<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderDestroyTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_to_users__for_not_authorized_users(): void
    {
        $order = factory(Order::class)->create();
        $this->deleteJson(route('body-shop.orders.destroy', $order), [])
            ->assertUnauthorized();
    }

    public function test_it_delete(): void
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

        $orderId = (int) $response['data']['id'];
        $typeOfWorkId = (int) $response['data']['types_of_work'][0]['id'];
        $typeOfWorkInventoryId = (int) $response['data']['types_of_work'][0]['inventories'][0]['id'];

        $inventory->refresh();
        $this->assertEquals(97, $inventory->quantity);
        $this->assertDatabaseHas(
            Transaction::TABLE_NAME,
            [
                'order_id' => $orderId,
                'inventory_id' => $inventory->id,
                'is_reserve' => true,
                'quantity' => 3,
            ]
        );


        $this->deleteJson(route('body-shop.orders.destroy', $orderId))
            ->assertNoContent();

        $inventory->refresh();
        $this->assertEquals(100, $inventory->quantity);
        $this->assertDatabaseMissing(
            Transaction::TABLE_NAME,
            [
                'order_id' => $orderId,
                'inventory_id' => $inventory->id,
            ]
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $orderId,
                'status' => Order::STATUS_DELETED,
                'status_before_deleting' => Order::STATUS_NEW,
            ]
        );

        $this->assertDatabaseHas(
            TypeOfWork::TABLE_NAME,
            [
                'order_id' => $orderId,
                'id' => $typeOfWorkId,
            ]
        );

        $this->assertDatabaseHas(
            TypeOfWorkInventory::TABLE_NAME,
            [
                'type_of_work_id' => $typeOfWorkId,
                'id' => $typeOfWorkInventoryId,
            ]
        );
    }

    public function test_it_delete_order_permanent_success()
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $order->delete();


        $this->deleteJson(route('body-shop.orders.delete-permanently', ['order' => $order->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
            ]
        );
    }

    public function test_it_delete_order_permanent_not_soft_deleted_order()
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();

        $this->deleteJson(route('body-shop.orders.delete-permanently', ['order' => $order->id]))
            ->assertNotFound();
    }
}
