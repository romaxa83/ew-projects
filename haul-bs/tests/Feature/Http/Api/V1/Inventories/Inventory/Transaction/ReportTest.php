<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\OrderType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected TransactionBuilder $transactionBuilder;
    protected Orders\BS\OrderBuilder $orderBsBuilder;
    protected Orders\Parts\OrderBuilder $orderPartsBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
        $this->orderBsBuilder = resolve(Orders\BS\OrderBuilder::class);
        $this->orderPartsBuilder = resolve(Orders\Parts\OrderBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $t_1 = $this->transactionBuilder->inventory($inventory)->create();
        $t_2 = $this->transactionBuilder->inventory($inventory)->create();
        $t_3 = $this->transactionBuilder->is_reserve(true)->inventory($inventory)->create();

        $this->getJson(route('api.v1.inventories.transactions.report'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'inventory_id',
                        'stock_number',
                        'name',
                        'is_inventory_deleted',
                        'date',
                        'operation_type',
                        'quantity',
                        'cost',
                        'price',
                        'invoice_number',
                        'category',
                        'supplier',
                        'order_id',
                        'order_type',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $t_2->id],
                    ['id' => $t_1->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 2,
                    'to' => 2,
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

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'page' => 2,
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 4,
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

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'per_page' => 2,
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

        $this->getJson(route('api.v1.inventories.transactions.report'))
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
    public function success_search_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder
            ->name('aaaaa')
            ->category(null)
            ->stock_number('111111111')
            ->create();
        $inventory_2 = $this->inventoryBuilder->name('bbbbb')->stock_number('211111111')->create();
        $inventory_3 = $this->inventoryBuilder->name('zzzzz')->stock_number('311111111')->create();

        $order = $this->orderBsBuilder->create();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder->inventory($inventory_1)->order($order)->create();
        $this->transactionBuilder->inventory($inventory_2)->create();
        $this->transactionBuilder->inventory($inventory_3)->create();
        $this->transactionBuilder->inventory($inventory_3)->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'search' => 'aaa'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $t_1->id,
                        'stock_number' => $inventory_1->stock_number,
                        'name' => $inventory_1->name,
                        'is_inventory_deleted' => false,
                        'operation_type' => $t_1->operation_type->value,
                        'quantity' => $t_1->quantity,
                        'cost' => $t_1->price,
                        'price' => null,
                        'invoice_number' => $t_1->invoice_number,
                        'category' => false,
                        'supplier' => $inventory_1->supplier->name,
                        'order_id' => $order->id,
                        'order_type' => OrderType::BS(),
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_stock_number()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder
            ->name('aaaaa')
            ->stock_number('111111111')
            ->create();
        $inventory_2 = $this->inventoryBuilder->name('bbbbb')->stock_number('211111111')->create();
        $inventory_3 = $this->inventoryBuilder->name('zzzzz')->stock_number('311111111')->create();

        $order = $this->orderPartsBuilder->create();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder
            ->operation_type(OperationType::SOLD)
            ->inventory($inventory_1)
            ->order($order)
            ->create();
        $this->transactionBuilder->inventory($inventory_2)->create();
        $this->transactionBuilder->inventory($inventory_3)->create();
        $this->transactionBuilder->inventory($inventory_3)->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'search' => 'aaa'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $t_1->id,
                        'stock_number' => $inventory_1->stock_number,
                        'name' => $inventory_1->name,
                        'is_inventory_deleted' => false,
                        'operation_type' => $t_1->operation_type->value,
                        'quantity' => $t_1->quantity,
                        'cost' => null,
                        'price' => $t_1->price,
                        'invoice_number' => $t_1->invoice_number,
                        'category' =>$inventory_1->category->name,
                        'supplier' => $inventory_1->supplier->name,
                        'order_id' => $order->id,
                        'order_type' => OrderType::Parts(),
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_category()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();
        $inventory_3 = $this->inventoryBuilder->create();

        /** @var $t_1 Transaction */
        $this->transactionBuilder->inventory($inventory_1)->create();
        $this->transactionBuilder->inventory($inventory_2)->create();
        $t_1 = $this->transactionBuilder->inventory($inventory_3)->create();
        $t_2 = $this->transactionBuilder->inventory($inventory_3)->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'category_id' => $inventory_3->category_id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $t_2->id,],
                    ['id' => $t_1->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_supplier()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();
        $inventory_3 = $this->inventoryBuilder->create();

        /** @var $t_1 Transaction */
        $this->transactionBuilder->inventory($inventory_1)->create();
        $this->transactionBuilder->inventory($inventory_2)->create();
        $t_1 = $this->transactionBuilder->inventory($inventory_3)->create();
        $t_2 = $this->transactionBuilder->inventory($inventory_3)->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'supplier_id' => $inventory_3->supplier_id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $t_2->id,],
                    ['id' => $t_1->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_operation_type()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();
        $inventory_3 = $this->inventoryBuilder->create();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder->inventory($inventory_1)->operation_type(OperationType::SOLD)->create();
        $this->transactionBuilder->inventory($inventory_2)->create();
        $this->transactionBuilder->inventory($inventory_3)->create();
        $t_2 = $this->transactionBuilder->inventory($inventory_3)->operation_type(OperationType::SOLD)->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'transaction_type' => OperationType::SOLD->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $t_2->id,],
                    ['id' => $t_1->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_from()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        $date = CarbonImmutable::now();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder->inventory($inventory_1)->transaction_date($date->subDays())->create();
        $this->transactionBuilder->inventory($inventory_2)->transaction_date($date->subDays(5))->create();
        $this->transactionBuilder->inventory($inventory_1)->transaction_date($date->subDays(4))->create();
        $t_2 = $this->transactionBuilder->inventory($inventory_2)->transaction_date($date->subDays(2))->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'date_from' => $date->subDays(3)->format('m/d/Y')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $t_1->id,],
                    ['id' => $t_2->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_to()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        $date = CarbonImmutable::now();

        /** @var $t_1 Transaction */
        $this->transactionBuilder->inventory($inventory_1)->transaction_date($date->subDays())->create();
        $t_1 = $this->transactionBuilder->inventory($inventory_2)->transaction_date($date->subDays(5))->create();
        $t_2 = $this->transactionBuilder->inventory($inventory_1)->transaction_date($date->subDays(4))->create();
        $this->transactionBuilder->inventory($inventory_2)->transaction_date($date->subDays(2))->create();

        $this->getJson(route('api.v1.inventories.transactions.report', [
            'date_to' => $date->subDays(3)->format('m/d/Y')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $t_2->id,],
                    ['id' => $t_1->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.transactions.report'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.transactions.report'));

        self::assertUnauthenticatedMessage($res);
    }
}
