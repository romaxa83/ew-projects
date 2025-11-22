<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Unit;
use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class InventoryUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_for_unauthorized_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->putJson(route('body-shop.inventories.update', $inventory))->assertUnauthorized();
    }

    public function test_it_update_by_bs_super_admin_with_same_quantity(): void
    {
        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'price_retail' => 30.00,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.inventories.update', $inventory), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $formRequest);
    }

    public function test_it_update_with_min_limit(): void
    {
        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'price_retail' => 30.00,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
            'min_limit' => 3,
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.inventories.update', $inventory), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $formRequest);
    }

    public function test_unique_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $stockNumber= 'JJH22';
        factory(Inventory::class)->create(['stock_number' => $stockNumber]);
        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => $stockNumber,
            'quantity' => $inventory->quantity,
            'price_retail' => 30.00,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
            'min_limit' => 3,
        ];

        $this->putJson(route('body-shop.inventories.update', $inventory), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_update_stock_number_with_dash(): void
    {
        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg34341-',
            'price_retail' => 30.00,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.inventories.update', $inventory), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $formRequest);
    }
}
