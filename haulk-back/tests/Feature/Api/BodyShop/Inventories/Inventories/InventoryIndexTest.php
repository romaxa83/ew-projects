<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.inventories.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('body-shop.inventories.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.inventories.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_body_shop_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.inventories.index'))
            ->assertOk();
    }

    public function test_default_ordering(): void
    {
        $this->loginAsBodyShopAdmin();

        factory(Inventory::class)->create([
            'quantity' => 0,
            'name' => 'AName',
        ]);
        factory(Inventory::class)->create([
            'quantity' => 10,
            'name' => 'CName',
        ]);
        factory(Inventory::class)->create([
            'quantity' => 3,
            'name' => 'BName',
        ]);

        $response = $this->getJson(route('body-shop.inventories.index'))
            ->assertOk();

        $this->assertEquals('BName', $response['data'][0]['name']);
        $this->assertEquals( 'CName', $response['data'][1]['name']);
        $this->assertEquals('AName', $response['data'][2]['name']);
    }
}
