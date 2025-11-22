<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Enums\Inventories\Transaction\DescribeType;
use App\Enums\Inventories\Transaction\OperationType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\TestCase;

class GenerateInvoiceTest extends TestCase
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
    public function success_generate()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->operation_type(OperationType::SOLD)
            ->describe(DescribeType::Sold)
            ->inventory($inventory)
            ->create();

        $this->getJson(route('api.v1.inventories.transactions.generate-invoice', [
            'id' => $transaction->id
        ]))
            ->assertOk()
        ;
    }

    /** @test */
    public function fail_operation_type_not_sold()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->operation_type(OperationType::PURCHASE)
            ->describe(DescribeType::Sold)
            ->inventory($inventory)
            ->create();

        $res = $this->getJson(route('api.v1.inventories.transactions.generate-invoice', [
            'id' => $transaction->id
        ]))
        ;

        self::assertErrorMsg($res, 'Error', Response::HTTP_NOT_FOUND);
    }


    /** @test */
    public function fail_not_found_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.transactions.generate-invoice', [
            'id' => 99999
        ]));

        self::assertErrorMsg($res, __("exceptions.inventories.transaction.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->operation_type(OperationType::SOLD)
            ->describe(DescribeType::Sold)
            ->create();

        $res = $this->getJson(route('api.v1.inventories.transactions.generate-invoice', [
            'id' => $transaction->id
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->operation_type(OperationType::SOLD)
            ->describe(DescribeType::Sold)
            ->create();

        $res = $this->getJson(route('api.v1.inventories.transactions.generate-invoice', [
            'id' => $transaction->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
