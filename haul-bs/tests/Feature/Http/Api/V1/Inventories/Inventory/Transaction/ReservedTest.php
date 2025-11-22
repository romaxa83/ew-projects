<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\OrderType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders;
use Tests\TestCase;

class ReservedTest extends TestCase
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

        $t_1 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $t_2 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $t_3 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        // not
        $t_4 = $this->transactionBuilder->is_reserve(false)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $t_5 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::PURCHASE)->inventory($inventory)->create();
        $this->transactionBuilder->create();

        $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]))
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

        $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();

        $this->getJson(route('api.v1.inventories.transactions.reserved', [
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

        $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();
        $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)->inventory($inventory)->create();

        $this->getJson(route('api.v1.inventories.transactions.reserved', [
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

        $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]))
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
    public function success_check_fields()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $order = $this->orderBsBuilder->create();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)
            ->inventory($inventory)
            ->order($order)
            ->create();

        $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $t_1->id,
                        'operation_type' => $t_1->operation_type->value,
                        'quantity' => $t_1->quantity,
                        'price' => $t_1->price_with_discount_and_tax ?? $t_1->price,
                        'invoice_number' => $t_1->invoice_number,
                        'inventory_id' => $t_1->inventory_id,
                        'order_id' => $order->id,
                        'order_type' => OrderType::BS(),
                        'comment' => $t_1->describe,
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_order_as_parts()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $order = $this->orderPartsBuilder->create();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)
            ->inventory($inventory)
            ->order($order)
            ->create();

        $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $t_1->id,
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
    public function success_without_order()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        /** @var $t_1 Transaction */
        $t_1 = $this->transactionBuilder->is_reserve(true)
            ->operation_type(OperationType::SOLD)
            ->inventory($inventory)
            ->create();

        $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $t_1->id,
                        'order_id' => null,
                        'order_type' => null,
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_not_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => 0]))
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

        $res = $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.transactions.reserved', ['id' => $inventory->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
