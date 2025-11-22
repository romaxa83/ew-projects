<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class InventoryShortlistTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        factory(Inventory::class)->create([
            'name' => 'Name1',
            'stock_number' => '123FGDNGK',
        ]);

        factory(Inventory::class)->create([
            'name' => 'Name2',
            'stock_number' => '5ame3',
        ]);

        factory(Inventory::class)->create([
            'name' => 'Name3',
            'stock_number' => '23424234',
        ]);


        $filter = ['q' => 'ame3'];
        $response = $this->getJson(route('body-shop.inventories.shortlist', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(2, $inventories);
    }

    public function test_search_by_id(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $inventory = factory(Inventory::class)->create();
        factory(Inventory::class)->create();
        factory(Inventory::class)->create();


        $filter = ['searchid' => $inventory->id];
        $response = $this->getJson(route('body-shop.inventories.shortlist', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(1, $inventories);
    }
}
