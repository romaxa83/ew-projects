<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Models\Inventories\Inventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
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
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $now = CarbonImmutable::now();

        $t_1 = $this->transactionBuilder->inventory($inventory)->transaction_date($now->subMinutes(3))->create();
        $t_2 = $this->transactionBuilder->inventory($inventory)->transaction_date($now->subMinutes(4))->create();
        $t_3 = $this->transactionBuilder->inventory($inventory)->transaction_date($now->subMinutes(5))->create();
        $t_4 = $this->transactionBuilder->is_reserve(true)->inventory($inventory)->create();
        $this->transactionBuilder->create();

        $this->getJson(route('api.v1.inventories.transactions', ['id' => $inventory->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'operation_type',
                        'quantity',
                        'price',
                        'invoice_number',
                        'inventory_id',
                        'order_id',
                        'comment',
                        'date',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $t_1->id],
                    ['id' => $t_2->id],
                    ['id' => $t_3->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'to' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->create();

        $this->getJson(route('api.v1.inventories.transactions', [
            'page' => 2,
            'id' => $inventory->id
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 3,
                    'to' => null,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_per_page()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->inventory($inventory)->create();
        $this->transactionBuilder->create();

        $this->getJson(route('api.v1.inventories.transactions', [
            'per_page' => 2,
            'id' => $inventory->id
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'per_page' => 2,
                    'to' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $this->getJson(route('api.v1.inventories.transactions', ['id' => $inventory->id]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'to' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_not_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.transactions', ['id' => 0]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'to' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.transactions', ['id' => $inventory->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.transactions', ['id' => $inventory->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
