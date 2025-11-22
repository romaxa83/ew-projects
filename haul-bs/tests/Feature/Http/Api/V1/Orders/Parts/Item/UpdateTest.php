<?php

namespace Tests\Feature\Http\Api\V1\Orders\Parts\Item;

use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected TransactionBuilder $transactionBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
    }

    /** @test */
    public function success_update()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->quantity(10)
            ->min_limit_price(null)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->price(20)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
            'discount' => 10,
        ];

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertFalse($model->source->isHaulkDepot());

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'discount' => $data['discount'],
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

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_update_without_discount()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->quantity(10)
            ->min_limit_price(null)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->price(20)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 5,
        ];

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertFalse($model->source->isHaulkDepot());

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'discount' => 0,
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
    }


    /** @test */
    public function success_update_as_ecomm()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->quantity(10)
            ->min_limit_price(null)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertTrue($model->source->isHaulkDepot());

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
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
    public function success_update_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $currentQty = 10;
        $oldQty = 5;
        $newQty = 4;
        $oldDiscount = 5;
        $newDiscount = 4;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->quantity($currentQty)
            ->min_limit_price(null)
            ->create();
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->discount($oldDiscount)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => $newQty,
            'discount' => $newDiscount,
        ];

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'discount' => $data['discount'],
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;

        $model->refresh();

        /** @var $history History */
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.update_item');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'inventory_name' => $inventory->name,
        ]);

        $this->assertEquals($history->details['items.'. $item->id . '.inventories.' . $inventory->id .'.quantity'], [
            'old' => $oldQty,
            'new' => $newQty,
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['items.'. $item->id . '.inventories.' . $inventory->id .'.discount'], [
            'old' => $oldDiscount,
            'new' => $newDiscount,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_update_changeReservedQuantityForOrder_decrease_order_draft_not_save_history()
    {
        $this->loginUserAsSuperAdmin();

        $currentQty = 20;
        $oldQty = 11;
        $newQty = 15;

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();
        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->quantity($currentQty)
            ->min_limit_price(null)
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => $newQty,
        ];

        $this->assertEmpty($inventory->histories);
        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;

        $transaction->refresh();
        $inventory->refresh();
        $model->refresh();

        $this->assertEmpty($model->histories);
        $this->assertEmpty($inventory->histories);

        $this->assertEquals($inventory->quantity, $currentQty);
    }

    /** @test */
    public function success_update_changeReservedQuantityForOrder_increase()
    {
        $user = $this->loginUserAsSuperAdmin();

        $currentQty = 20;
        $oldQty = 11;
        $newQty = 5;

        /** @var $model Order */
        $model = $this->orderBuilder->create();
        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->quantity($currentQty)
            ->min_limit_price(null)
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => $newQty,
        ];

        $this->assertEmpty($inventory->histories);

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;

        $transaction->refresh();
        $inventory->refresh();

        $this->assertEquals($transaction->quantity, $newQty);
        $this->assertEquals($inventory->quantity, $currentQty + ($oldQty - $newQty));

        $history = $inventory->histories[1];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reduced_from_order');

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

        $this->assertEquals($history->details, [
            "quantity" => [
                'old' => $currentQty ,
                'new' => $currentQty + ($oldQty - $newQty),
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_update_changeReservedQuantityForOrder_increase_as_not_if_draft_order()
    {
        $this->loginUserAsSuperAdmin();

        $currentQty = 20;
        $oldQty = 11;
        $newQty = 5;

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();
        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->min_limit_price(null)
            ->quantity($currentQty)
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty($oldQty)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => $newQty,
        ];

        $this->assertEmpty($inventory->histories);

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;

        $transaction->refresh();
        $inventory->refresh();

        $this->assertEquals($inventory->quantity, $currentQty);
        $this->assertEmpty($inventory->histories);
    }

    /** @test */
    public function success_update_price_with_discount_but_not_min_price()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->price_retail(12)
            ->min_limit_price(null)
            ->quantity(10)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder
            ->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->discount(0)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 5,
            'discount' => 20,
        ];

        $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'quantity' => $data['quantity'],
                    'discount' => $data['discount'],
                    'inventory' => [
                        'id' => $data['inventory_id']
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function fail_update_price_with_discount_less_min_price()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder
            ->price_retail(12)
            ->min_limit_price(10)
            ->quantity(10)
            ->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder
            ->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->discount(0)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 5,
            'discount' => 20,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
        ;

        self::assertValidationMsg($res,
            __("validation.custom.order.parts.discounted_price_cannot_be_less_than_min_price", [
                'discounted_price' => '9.6',
                'min_price' => '10.00',
            ]),
            'discount')
        ;
    }

    /** @test */
    public function fail_update_order_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->quantity(10)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
        ;

        self::assertErrorMsg(
            $res,
            __("exceptions.orders.parts.cant_edit_paid"),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /** @test */
    public function fail_delete_order_is_returned()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->quantity(10)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)->status(OrderStatus::Returned())->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_edit"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id + 999,
            'itemId' => $item->id
        ]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_item()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id +999
        ]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found_item"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $data = [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
        ];

        $res = $this->postJson(route('api.v1.orders.parts.update-item', [
            'id' => $model->id + 999,
            'itemId' => $item->id
        ]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
