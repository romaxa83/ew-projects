<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Enums\Inventories\Transaction\OperationType;
use App\Events\Events\Inventories\Inventories\ChangeQuantityInventory;
use App\Events\Listeners\Inventories\Inventories\SyncEComChangeQuantityInventoryListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->inventoryBuilder = resolve(InventoryBuilder::class);

        parent::setUp();

        $this->data = [
            'quantity' => 3,
            'date' => "02/10/2004",
            'cost' => 3.9,
            'invoice_number' => '3TYYYY4',
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([ChangeQuantityInventory::class]);

        $user = $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;

        $totalAmount = $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'operation_type' => OperationType::PURCHASE->value,
                    'quantity' => data_get($data, 'quantity'),
                    'price' => data_get($data, 'cost'),
                    'invoice_number' => data_get($data, 'invoice_number'),
                    'inventory_id' => $inventory->id,
                    'order_id' => null,
                    'order_type' => null,
                    'describe' => null,
                    'payment_date' => null,
                    'payment_method' => null,
                    'tax' => null,
                    'discount' => null,
                    'first_name' => null,
                    'last_name' => null,
                    'company_name' => null,
                    'phone' => null,
                    'email' => null,

                ],
            ])
            ->json('data.total_amount')
        ;

        $inventory->refresh();

        $model = $inventory->transactions[0];

        $this->assertEquals($inventory->quantity, data_get($data, 'quantity') + 10);
        $this->assertEquals($model->getTotalAmount(), $totalAmount);

        $history = $inventory->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_increased');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'quantity' => [
                'old' => 10,
                'new' => data_get($data, 'quantity') + 10,
                'type' => 'updated',
            ],
        ]);

        // increaseQuantity
        Event::assertDispatched(fn (ChangeQuantityInventory $event) =>
            $event->getModel()->id === $inventory->id
        );
        Event::assertListening(
            ChangeQuantityInventory::class,
            SyncEComChangeQuantityInventoryListener::class
        );
    }

    /** @test */
    public function success_create_not_event()
    {
        Event::fake([ChangeQuantityInventory::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->for_shop(false)->quantity(10)->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data)
        ;

        Event::assertNotDispatched(ChangeQuantityInventory::class);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data['quantity'] = null;

        $this->assertEmpty($inventory->transactions);

        $res = $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.quantity')]),
            'quantity'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;

        $this->assertEmpty($inventory->transactions);

        $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $inventory->refresh();

        $this->assertEmpty($inventory->transactions);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['quantity', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.quantity']],
            ['cost', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.cost']],
            ['date', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.date']],
        ];
    }

    /** @test */
    public function fail_not_found_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => 0
        ]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $res = $this->postJson(route('api.v1.inventories.transactions.purchase', [
            'id' => $inventory->id
        ]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
