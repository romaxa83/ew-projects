<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Enums\Inventories\Transaction\DescribeType;
use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Inventories\Transaction\PaymentMethod;
use App\Events\Events\Inventories\Inventories\ChangeQuantityInventory;
use App\Events\Listeners\Inventories\Inventories\SyncEComChangeQuantityInventoryListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Inventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class SoldTest extends TestCase
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
            'price' => 3.9,
            'invoice_number' => '3TYYYY4',
            'describe' => DescribeType::Sold->value,
            'discount' => 1.5,
            'tax' => 0.5,
            'payment_date' => "02/10/2024",
            'payment_method' => PaymentMethod::Cash->value,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'company_name' => 'company',
            'phone' => '11499999999',
            'email' => 'test@test.com',
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([ChangeQuantityInventory::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;

        $totalAmount = $this->postJson(route('api.v1.inventories.transactions.sold', [
            'id' => $inventory->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'operation_type' => OperationType::SOLD->value,
                    'quantity' => data_get($data, 'quantity'),
                    'price' => data_get($data, 'price'),
                    'invoice_number' => data_get($data, 'invoice_number'),
                    'inventory_id' => $inventory->id,
                    'order_id' => null,
                    'describe' => data_get($data, 'describe'),
                    'payment_method' => data_get($data, 'payment_method'),
                    'tax' => data_get($data, 'tax'),
                    'discount' => data_get($data, 'discount'),
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'company_name' => data_get($data, 'company_name'),
                    'phone' => data_get($data, 'phone'),
                    'email' => data_get($data, 'email'),

                ],
            ])
            ->json('data.total_amount')
        ;

        $inventory->refresh();

        $model = $inventory->transactions[0];

        $this->assertEquals($inventory->quantity, 10 - data_get($data, 'quantity'));
        $this->assertEquals($model->getTotalAmount(), $totalAmount);

        // decreaseQuantity
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
        $inventory = $this->inventoryBuilder->quantity(10)->for_shop(false)->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.transactions.sold', [
            'id' => $inventory->id
        ]), $data)
        ;

        Event::assertNotDispatched(ChangeQuantityInventory::class);
    }

    /** @test */
    public function success_create_only_required_field()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;
        $data['describe'] = DescribeType::Defect->value;
        unset(
            $data['discount'],
            $data['tax'],
            $data['invoice_number'],
            $data['payment_date'],
            $data['payment_method'],
            $data['first_name'],
            $data['last_name'],
            $data['company_name'],
            $data['phone'],
            $data['email'],
        );

        $totalAmount = $this->postJson(route('api.v1.inventories.transactions.sold', [
            'id' => $inventory->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'operation_type' => OperationType::SOLD->value,
                    'quantity' => data_get($data, 'quantity'),
                    'price' => data_get($data, 'price'),
                    'invoice_number' => null,
                    'inventory_id' => $inventory->id,
                    'order_id' => null,
                    'describe' => data_get($data, 'describe'),
                    'payment_method' => null,
                    'payment_date' => null,
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

        $this->assertEquals($inventory->quantity, 10 - data_get($data, 'quantity'));
        $this->assertEquals($model->getTotalAmount(), $totalAmount);

        $history = $inventory->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_decreased');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'operation_type' => [
                'old' => null,
                'new' => OperationType::SOLD->value,
                'type' => 'added',
            ],
            'price' => [
                'old' => null,
                'new' => data_get($data, 'price'),
                'type' => 'added',
            ],
            'quantity' => [
                'old' => 10,
                'new' => 10 - data_get($data, 'quantity'),
                'type' => 'updated',
            ],
            'describe' => [
                'old' => null,
                'new' => data_get($data, 'describe'),
                'type' => 'added',
            ],
            'transaction_date' => [
                'old' => null,
                'new' => CarbonImmutable::createFromFormat('m/d/Y', data_get($data, 'date'))->format('Y-m-d'),
                'type' => 'added',
            ],
            'is_reserve' => [
                'old' => null,
                'new' => false,
                'type' => 'added',
            ],
        ]);
    }

    /** @test */
    public function success_create_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.transactions.sold', [
            'id' => $inventory->id
        ]), $data)
        ;

        $inventory->refresh();

        $history = $inventory->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_decreased_sold');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'operation_type' => [
                'old' => null,
                'new' => OperationType::SOLD->value,
                'type' => 'added',
            ],
            'price' => [
                'old' => null,
                'new' => data_get($data, 'price'),
                'type' => 'added',
            ],
            'quantity' => [
                'old' => 10,
                'new' => 10 - data_get($data, 'quantity'),
                'type' => 'updated',
            ],
            'describe' => [
                'old' => null,
                'new' => data_get($data, 'describe'),
                'type' => 'added',
            ],
            'transaction_date' => [
                'old' => null,
                'new' => CarbonImmutable::createFromFormat('m/d/Y', data_get($data, 'date'))->format('Y-m-d'),
                'type' => 'added',
            ],
            'is_reserve' => [
                'old' => null,
                'new' => false,
                'type' => 'added',
            ],
            'discount' => [
                'old' => null,
                'new' => data_get($data, 'discount'),
                'type' => 'added',
            ],
            'tax' => [
                'old' => null,
                'new' => data_get($data, 'tax'),
                'type' => 'added',
            ],
            'payment_date' => [
                'old' => null,
                'new' => CarbonImmutable::createFromFormat('m/d/Y', data_get($data, 'payment_date'))->format('Y-m-d'),
                'type' => 'added',
            ],
            'first_name' => [
                'old' => null,
                'new' => data_get($data, 'first_name'),
                'type' => 'added',
            ],
            'last_name' => [
                'old' => null,
                'new' => data_get($data, 'last_name'),
                'type' => 'added',
            ],
            'phone' => [
                'old' => null,
                'new' => data_get($data, 'phone'),
                'type' => 'added',
            ],
            'email' => [
                'old' => null,
                'new' => data_get($data, 'email'),
                'type' => 'added',
            ],
            'company_name' => [
                'old' => null,
                'new' => data_get($data, 'company_name'),
                'type' => 'added',
            ],
            'payment_method' => [
                'old' => null,
                'new' => data_get($data, 'payment_method'),
                'type' => 'added',
            ],
        ]);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data['quantity'] = null;

        $this->assertEmpty($inventory->transactions);

        $res = $this->postJson(route('api.v1.inventories.transactions.sold', [
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

        $this->postJson(route('api.v1.inventories.transactions.sold', [
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

        $res = $this->postJson(route('api.v1.inventories.transactions.sold', [
            'id' => $inventory->id
        ]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['quantity', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.quantity']],
            ['quantity', 100, 'validation.max.numeric', ['attribute' => 'validation.attributes.purchase.quantity', 'max' => 10]],
            ['price', null, 'validation.required', ['attribute' => 'validation.attributes.price']],
            ['date', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.date']],
            ['describe', null, 'validation.required', ['attribute' => 'validation.attributes.describe']],
            ['describe', 'wrong', 'validation.in', ['attribute' => 'validation.attributes.describe']],
            ['discount', 0, 'validation.min.numeric', ['attribute' => 'validation.attributes.discount', 'min' => '0.01']],
            ['tax', 0, 'validation.min.numeric', ['attribute' => 'validation.attributes.tax', 'min' => '0.01']],
            ['invoice_number', null, 'validation.required_if', [
                'attribute' => 'validation.attributes.invoice_number',
                'other' => 'validation.attributes.describe',
                'value' => 'validation.attributes.sold'
            ]],
            ['payment_date', null, 'validation.required_if', [
                'attribute' => 'validation.attributes.payment_date',
                'other' => 'validation.attributes.describe',
                'value' => 'validation.attributes.sold'
            ]],
            ['payment_method', null, 'validation.required_if', [
                'attribute' => 'validation.attributes.payment_method',
                'other' => 'validation.attributes.describe',
                'value' => 'sold'
            ]],
            ['payment_method', 'wrong', 'validation.in', ['attribute' => 'validation.attributes.payment_method']],
            ['phone', null, 'validation.required_if', [
                'attribute' => 'validation.attributes.phone',
                'other' => 'validation.attributes.describe',
                'value' => 'sold'
            ]],
            ['phone', 'wrong', 'validation.custom.phone.phone_rule', ['attribute' => 'validation.attributes.phone']],
            ['email', null, 'validation.required_if', [
                'attribute' => 'validation.attributes.email',
                'other' => 'validation.attributes.describe',
                'value' => 'sold'
            ]],
            ['email', 'wrong', 'validation.email', ['attribute' => 'validation.attributes.email']],
        ];
    }

    /** @test */
    public function fail_not_found_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.transactions.sold', [
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

        $res = $this->postJson(route('api.v1.inventories.transactions.sold', [
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

        $res = $this->postJson(route('api.v1.inventories.transactions.sold', [
            'id' => $inventory->id
        ]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
