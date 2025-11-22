<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderRestoreTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_to_users_for_not_authorized_users(): void
    {
        $order = factory(Order::class)->create();
        $this->putJson(route('body-shop.orders.restore', $order->id))
            ->assertUnauthorized();
    }

    public function test_it_restore(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $order->delete();

        $this->putJson(route('body-shop.orders.restore', $order->id))
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_NEW,
                'status_before_deleting' => null,
                'deleted_at' => null,
            ]
        );
    }

    public function test_it_restore_with_enough_inventory_quantity(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);
        $typeOfWorkInventory = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);
        $order->delete();

        $this->putJson(route('body-shop.orders.restore', $order->id))
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_NEW,
                'status_before_deleting' => null,
                'deleted_at' => null,
            ]
        );
    }

    public function test_it_restore_with_not_enough_inventory_quantity(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 1]);
        $typeOfWorkInventory = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);
        $order->delete();

        $this->putJson(route('body-shop.orders.restore', $order->id))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_restore_not_deleted_order(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();

        $this->putJson(route('body-shop.orders.restore', $order->id))
            ->assertNotFound();
    }

    public function test_it_restore_do_not_touch_other_deleted_orders(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order1 = factory(Order::class)->create();
        $order1->delete();

        $order2 = factory(Order::class)->create();
        $order2->delete();

        $this->putJson(route('body-shop.orders.restore', $order1->id))
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order1->id,
                'status' => Order::STATUS_NEW,
                'status_before_deleting' => null,
                'deleted_at' => null,
            ]
        );
        $order2->refresh();
        $this->assertNotNull($order2->deleted_at);
    }
}
