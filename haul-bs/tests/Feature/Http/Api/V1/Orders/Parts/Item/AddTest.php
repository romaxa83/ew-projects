<?php

namespace Feature\Http\Api\V1\Orders\Parts\Item;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\OrderType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class AddTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected InventoryBuilder $inventoryBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_add()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        /** @var $inventory_2 Inventory */
        $inventory_2 = $this->inventoryBuilder
            ->old_price(76)
            ->weight(100)
            ->delivery_cost(22)
            ->min_limit_price(null)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder
            ->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory_2->id,
            'quantity' => 9,
            'discount' => 10,
        ];

        $this->assertCount(1, $model->items);

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertFalse($model->source->isHaulkDepot());

        $price_with_discount = round(price_with_discount($inventory_2->price_retail, $data['discount'])
            + price_with_discount($inventory_2->delivery_cost, $data['discount']), 2);

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'quantity' => $data['quantity'],
                    'free_shipping' => true,
                    'price' => $price_with_discount,
                    'price_old' => $inventory_2->old_price,
                    'delivery_cost' => $inventory_2->delivery_cost,
                    'discount' => $data['discount'],
                    'is_overload' => false,
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->total_amount);
        $this->assertNotNull($model->paid_amount);
        $this->assertNotNull($model->debt_amount);

        $this->assertCount(2, $model->items);

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_add_source_ecomm_and_without_discount()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->min_limit_price(null)
            ->quantity(10)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 9,
        ];

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertTrue($model->source->isHaulkDepot());

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'quantity' => $data['quantity'],
                    'discount' => 0,
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->total_amount);
        $this->assertNotNull($model->paid_amount);
        $this->assertNotNull($model->debt_amount);

        $this->assertCount(1, $model->items);

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_UPDATE
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function success_add_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder
            ->weight(200)
            ->min_limit_price(null)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory_2->id,
            'quantity' => 9,
            'discount' => 10,
        ];

        $this->assertCount(1, $model->items);

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'free_shipping' => false,
                    'is_overload' => true,
                ],
            ])
        ;

        $model->refresh();
        $item = $model->items->where('inventory_id', $inventory_2->id)->first();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.add_item');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'inventory_name' => $inventory_2->name,
        ]);

        $this->assertEquals($history->details['items.'. $item->id . '.inventories.' . $inventory_2->id .'.name'], [
            'old' => null,
            'new' => $inventory_2->name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['items.'. $item->id . '.inventories.' . $inventory_2->id .'.quantity'], [
            'old' => null,
            'new' => $item->qty,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['items.'. $item->id . '.inventories.' . $inventory_2->id .'.discount'], [
            'old' => null,
            'new' => $data['discount'],
            'type' => 'added',
        ]);
    }

    /** @test */
    public function success_add_not_history_if_draft_order()
    {
        $user = $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->min_limit_price(null)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory_2->id,
            'quantity' => 9,
        ];

        $this->assertCount(1, $model->items);

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        $model->refresh();

        $this->assertEmpty($model->histories);
    }

    /** @test */
    public function success_add_check_reserve_for_order()
    {
        $user = $this->loginUserAsSuperAdmin();

        $wasQty = 10;
        $reserveQty = 4;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->quantity($wasQty)
            ->min_limit_price(null)
            ->create();
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => $reserveQty,
        ];

        $this->assertEmpty($inventory->transactions);
        $this->assertEmpty($inventory->histories);

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        $model->refresh();
        $inventory->refresh();

        $this->assertCount(1, $inventory->transactions);

        $this->assertNull($inventory->transactions[0]->order_id);
        $this->assertEquals($inventory->transactions[0]->inventory_id, $inventory->id);
        $this->assertEquals($inventory->transactions[0]->operation_type->value, OperationType::SOLD->value);
        $this->assertEquals($inventory->transactions[0]->invoice_number, $model->order_number);
        $this->assertEquals($inventory->transactions[0]->price, $inventory->price_retail);
        $this->assertEquals($inventory->transactions[0]->quantity, $reserveQty);
        $this->assertEquals($inventory->transactions[0]->order_parts_id, $model->id);
        $this->assertEquals($inventory->transactions[0]->order_type, OrderType::Parts);
        $this->assertTrue($inventory->transactions[0]->is_reserve);
        $this->assertNull($inventory->transactions[0]->order_id);

        $this->assertEquals($inventory->quantity, $wasQty - $reserveQty);

        $this->assertCount(1, $inventory->histories);

        $this->assertEquals($inventory->transactions[0]->order_parts_id, $model->id);

        /** @var $history History */
        $history = $inventory->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);

        $this->assertEquals($history->details['quantity'], [
            'old' => $wasQty,
            'new' => $wasQty - $reserveQty,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_add_check_reserve_for_order_as_not_if_draft_order()
    {
        $this->loginUserAsSuperAdmin();

        $wasQty = 10;
        $reserveQty = 4;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->min_limit_price(null)
            ->quantity($wasQty)
            ->create();
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => $reserveQty,
        ];

        $this->assertEmpty($inventory->transactions);
        $this->assertEmpty($inventory->histories);

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        $model->refresh();
        $inventory->refresh();

        $this->assertEmpty($inventory->transactions);
        $this->assertEmpty($inventory->histories);
        $this->assertEquals($inventory->quantity, $wasQty);
    }

    /** @test */
    public function fail_add_price_with_discount_less_than_min_limit_price()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->price_retail(15)
            ->min_limit_price(14)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 9,
            'discount' => 30,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res,
            __("validation.custom.order.parts.discounted_price_cannot_be_less_than_min_price", [
                'discounted_price' => '10.5',
                'min_price' => '14.00',
            ]),
            'discount')
        ;
    }

    /** @test */
    public function not_fail_add_price_with_discount_less_than_min_limit_price_if_discount_zero()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->price_retail(15)
            ->min_limit_price(20)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 9,
            'discount' => 0,
        ];

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'quantity' => $data['quantity'],
                    'discount' => $data['discount'],
                    'is_overload' => false,
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function fail_add_order_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory_2->id,
            'quantity' => 9,
        ];

        $this->assertCount(1, $model->items);

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_edit_paid"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_add_order_is_returned()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)->status(OrderStatus::Returned())->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory_2->id,
            'quantity' => 9,
        ];

        $this->assertCount(1, $model->items);

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_edit"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_enough_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->quantity(1)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder
            ->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory_2->id,
            'quantity' => 9,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data)
        ;

        $this->assertValidationMsg(
            $res,
            __("validation.custom.order.parts.few_quantities"),
            'quantity'
        );
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'quantity' => null,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data, [
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

        $inventory = $this->inventoryBuilder->create();
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 1,
        ];

        $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 9,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $inventory = $this->inventoryBuilder->create();
        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 9,
        ];

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.add-item', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
