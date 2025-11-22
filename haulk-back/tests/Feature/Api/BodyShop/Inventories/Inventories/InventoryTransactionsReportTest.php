<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class InventoryTransactionsReportTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    private Inventory $inventory1;
    private Inventory $inventory2;

    public function test_search(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();

        $filter = ['q' => 'GFHD34'];
        $response = $this->getJson(route('body-shop.inventories.report', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(3, $inventories);

        $filter = ['q' => 'Name1'];
        $response = $this->getJson(route('body-shop.inventories.report', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(4, $inventories);
    }

    public function test_filter_by_category(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();

        $filter = ['category_id' => $this->inventory1->category_id];
        $response = $this->getJson(route('body-shop.inventories.report', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(3, $inventories);
    }

    public function test_filter_by_transaction_type(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();

        $filter = ['transaction_type' => Transaction::OPERATION_TYPE_PURCHASE];
        $response = $this->getJson(route('body-shop.inventories.report', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(4, $inventories);
    }

    public function test_filter_by_supplier(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();

        $filter = ['supplier_id' => $this->inventory2->supplier_id];
        $response = $this->getJson(route('body-shop.inventories.report', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(4, $inventories);
    }

    public function test_filter_by_date(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();

        $filter = [
            'date_from' => now()->addDays(-3)->format('m/d/Y'),
            'date_to' => now()->addDays(-2)->format('m/d/Y'),
        ];
        $response = $this->getJson(route('body-shop.inventories.report', $filter))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(2, $inventories);
    }

    public function test_with_deleted_inventory(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();
        $this->inventory1->delete();

        $response = $this->getJson(route('body-shop.inventories.report'))
            ->assertOk();

        $inventories = $response->json('data');
        $this->assertCount(7, $inventories);
    }

    public function test_report_total(): void
    {
        $this->createTransactions();
        $this->loginAsBodyShopSuperAdmin();

        $response = $this->getJson(route('body-shop.inventories.report-total'))
            ->assertOk();

        $this->assertNotEmpty($response['data']['price_total']);
        $this->assertNotEmpty($response['data']['cost_total']);

    }

    private function createTransactions(): void
    {
        $this->inventory1 = factory(Inventory::class)->create([
            'stock_number' => 'GFHD34',
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
        ]);
        $this->inventory2 = factory(Inventory::class)->create([
            'name' => 'Name1',
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
        ]);

        factory(Transaction::class)->create(['inventory_id' => $this->inventory1, 'operation_type' => Transaction::OPERATION_TYPE_PURCHASE]);
        factory(Transaction::class)->create([
            'inventory_id' => $this->inventory1,
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'transaction_date' => now()->addDays(-3),
        ]);
        factory(Transaction::class)->create(['inventory_id' => $this->inventory1, 'operation_type' => Transaction::OPERATION_TYPE_PURCHASE]);

        factory(Transaction::class)->create(['inventory_id' => $this->inventory2, 'operation_type' => Transaction::OPERATION_TYPE_PURCHASE]);
        factory(Transaction::class)->create([
            'inventory_id' => $this->inventory2,
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'transaction_date' => now()->addDays(-2),
        ]);
        factory(Transaction::class)->create(['inventory_id' => $this->inventory2, 'operation_type' => Transaction::OPERATION_TYPE_SOLD]);
        factory(Transaction::class)->create(['inventory_id' => $this->inventory2, 'operation_type' => Transaction::OPERATION_TYPE_PURCHASE]);
    }
}
