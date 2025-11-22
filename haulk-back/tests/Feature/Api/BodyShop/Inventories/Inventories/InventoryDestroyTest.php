<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork as OrderTypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory as OrderTypeOfWorkInventory;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class InventoryDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertForbidden();
    }

    public function test_it_delete_out_of_stock(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
        $this->assertNotEmpty($inventory->deleted_at);
    }

    public function test_it_delete_in_stock(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 10]);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
    }

    public function test_it_delete_with_types_of_work(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $typeOfWork = factory(TypeOfWork::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
    }

    public function test_it_delete_with_new_orders(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $order = factory(Order::class)->create([
            'status' => Order::STATUS_NEW,
            'status_changed_at' => now(),
        ]);
        $typeOfWork = factory(OrderTypeOfWork::class)->create(['order_id' => $order->id]);
        factory(OrderTypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
    }

    public function test_it_delete_with_in_process_orders(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $order = factory(Order::class)->create([
            'status' => Order::STATUS_IN_PROCESS,
            'status_changed_at' => now(),
        ]);
        $typeOfWork = factory(OrderTypeOfWork::class)->create(['order_id' => $order->id]);
        factory(OrderTypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
    }

    public function test_it_delete_with_finished_order_less_than_24_hours(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $order = factory(Order::class)->create([
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now(),
        ]);
        $typeOfWork = factory(OrderTypeOfWork::class)->create(['order_id' => $order->id]);
        factory(OrderTypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
    }

    public function test_it_delete_with_finished_order_more_than_24_hours(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $order = factory(Order::class)->create([
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now()->addDays(-25),
        ]);
        $typeOfWork = factory(OrderTypeOfWork::class)->create(['order_id' => $order->id]);
        factory(OrderTypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
        $inventory->refresh();
        $this->assertNotEmpty($inventory->deleted_at);
    }

    public function test_it_delete_with_deleted_orders(): void
    {
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $order = factory(Order::class)->create([
            'status' => Order::STATUS_IN_PROCESS,
            'status_changed_at' => now(),
        ]);
        $typeOfWork = factory(OrderTypeOfWork::class)->create(['order_id' => $order->id]);
        factory(OrderTypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $order->delete();

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventories.destroy', $inventory))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $inventory->refresh();
        $this->assertDatabaseHas(Inventory::TABLE_NAME, $inventory->getAttributes());
    }
}
