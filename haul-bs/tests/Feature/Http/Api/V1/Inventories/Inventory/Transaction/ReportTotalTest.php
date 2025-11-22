<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\TestCase;

class ReportTotalTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected TransactionBuilder $transactionBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
    }

    /** @test */
    public function success_data()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->is_reserve(true)->inventory($inventory)->create();

        $this->getJson(route('api.v1.inventories.transactions.report-total'))
            ->assertJsonStructure([
                'data' => [
                    'price_total',
                    'cost_total',
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.transactions.report-total'))
            ->assertJsonStructure([
                'data' => [
                    'price_total',
                    'cost_total',
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.transactions.report-total'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.transactions.report-total'));

        self::assertUnauthenticatedMessage($res);
    }
}
