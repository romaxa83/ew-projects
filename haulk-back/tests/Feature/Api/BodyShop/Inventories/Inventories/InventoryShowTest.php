<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_for_unauthorized_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->getJson(route('body-shop.inventories.show', $inventory))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->loginAsCarrierAdmin();

        $this->getJson(route('body-shop.inventories.show', $inventory))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.inventories.show', $inventory))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'stock_number',
                'category_id',
                'quantity',
                'price_retail',
                'supplier_id',
                'notes',
                'min_limit',
                'hasRelatedTypesOfWork',
                'hasRelatedOpenOrders',
                'hasRelatedDeletedOrders',
            ]]);

        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.inventories.show', $inventory))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'stock_number',
                'category_id',
                'quantity',
                'price_retail',
                'supplier_id',
                'notes',
                'min_limit',
                'hasRelatedTypesOfWork',
                'hasRelatedOpenOrders',
                'hasRelatedDeletedOrders',
            ]]);
    }
}
