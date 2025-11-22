<?php

namespace Tests\Feature\Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class InventoryIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $inventory1 = factory(Inventory::class)->create([
            'name' => 'Name1',
            'stock_number' => '123FGDNGK',
        ]);

        $inventory2 = factory(Inventory::class)->create([
            'name' => 'Name2',
            'stock_number' => '123FGKGKG',
        ]);

        $inventory3 = factory(Inventory::class)->create([
            'name' => 'Name3',
            'stock_number' => '567567',
        ]);


        $filter = ['q' => 'Name3'];
        $response = $this->getJson(route('body-shop.inventories.index', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(1, $inventories);
        $this->assertEquals($inventory3->id, $inventories[0]['id']);

        $filter = ['q' => '123FG'];
        $response = $this->getJson(route('body-shop.inventories.index', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(2, $inventories);
    }

    public function test_filter_by_category(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $category1 = factory(Category::class)->create();
        factory(Inventory::class)->times(3)->create(['category_id' => $category1->id]);

        $category2 = factory(Category::class)->create();
        factory(Inventory::class)->times(5)->create(['category_id' => $category2->id]);


        $filter = ['category_id' => $category1->id];
        $response = $this->getJson(route('body-shop.inventories.index', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(3, $inventories);
    }

    public function test_filter_by_supplier(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $supplier1 = factory(Supplier::class)->create();
        factory(Inventory::class)->times(3)->create(['supplier_id' => $supplier1->id]);

        $supplier2 = factory(Supplier::class)->create();
        factory(Inventory::class)->times(5)->create(['supplier_id' => $supplier2->id]);

        $filter = ['supplier_id' => $supplier1->id];
        $response = $this->getJson(route('body-shop.inventories.index', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(3, $inventories);
    }

    public function test_filter_by_status(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        factory(Inventory::class)->times(3)->create(['quantity' => 10]);
        factory(Inventory::class)->times(5)->create(['quantity' => 0]);

        $filter = ['status' => Inventory::STATUS_OUT_OF_STOCK];
        $response = $this->getJson(route('body-shop.inventories.index', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(5, $inventories);
    }
}
