<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryTransactionsIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->getJson(route('body-shop.inventories.transactions', $inventory))
            ->assertUnauthorized();

        $this->getJson(route('body-shop.inventories.reserve', $inventory))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $inventory = factory(Inventory::class)->create();
        $this->getJson(route('body-shop.inventories.transactions', $inventory))
            ->assertForbidden();

        $this->getJson(route('body-shop.inventories.reserve', $inventory))
            ->assertForbidden();
    }

    public function test_it_show_transactions(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create();
        $inventory2 = factory(Inventory::class)->create();
        factory(Transaction::class)->create(['inventory_id' => $inventory->id]);
        factory(Transaction::class)->create(['inventory_id' => $inventory->id, 'operation_type' => Transaction::OPERATION_TYPE_SOLD]);
        factory(Transaction::class)->create(['inventory_id' => $inventory2->id]);
        factory(Transaction::class)->create(['inventory_id' => $inventory->id, 'is_reserve' => true]);

        $response = $this->getJson(route('body-shop.inventories.transactions', $inventory))
            ->assertOk();

        $this->assertCount(2, $response['data']);
    }

    public function test_it_show_reserve(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create();
        $inventory2 = factory(Inventory::class)->create();
        factory(Transaction::class)->create(['inventory_id' => $inventory->id]);
        factory(Transaction::class)->create(['inventory_id' => $inventory->id, 'operation_type' => Transaction::OPERATION_TYPE_SOLD]);
        factory(Transaction::class)->create(['inventory_id' => $inventory2->id]);
        factory(Transaction::class)->create(['inventory_id' => $inventory->id, 'is_reserve' => true]);
        factory(Transaction::class)->create([
            'inventory_id' => $inventory->id,
            'is_reserve' => true,
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
        ]);

        $response = $this->getJson(route('body-shop.inventories.reserve', $inventory))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }
}
