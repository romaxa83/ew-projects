<?php

namespace Feature\Http\Api\V1\Orders\Parts\Action;

use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\DeliveryStatus;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\Parts\DeliveryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ChangeStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;
    protected TransactionBuilder $transactionBuilder;
    protected DeliveryBuilder $deliveryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
        $this->deliveryBuilder = resolve(DeliveryBuilder::class);
    }

    public static function validateNewStatus(): array
    {
        return [
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /** @test */
    public function success_change_in_progress_to_sent()
    {
        Event::fake([RequestToEcom::class]);

        $user = $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $now = CarbonImmutable::now();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())
            ->source(OrderSource::Haulk_Depot)
            ->sales_manager($sales)->create();

        $data['status'] = OrderStatus::Sent->value;
        $data['sent_data'] = [
            [
                'delivery_method' => DeliveryMethod::Our_delivery(),
                'delivery_cost' => 2.9,
                'date_sent' => $now->format('Y-m-d'),
                'tracking_number' => '67686',
            ]
        ];

        $this->assertTrue($model->status->isInProcess());
        $this->assertEmpty($model->deliveries);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Sent(),
                    'deliveries' => [
                        [
                            'status' => DeliveryStatus::Sent(),
                            'delivery_method' => $data['sent_data'][0]['delivery_method'],
                            'delivery_cost' => $data['sent_data'][0]['delivery_cost'],
                            'tracking_number' => $data['sent_data'][0]['tracking_number'],
                        ]
                    ],
                ],
            ])
        ;

        $model->refresh();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.status_changed');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'status' => OrderStatus::Sent->value,
        ]);

        $delivery = $model->deliveries[0];

        $this->assertEquals($history->details['status'], [
            'old' => OrderStatus::In_process(),
            'new' => $model->status->value,
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.delivery_method'], [
            'old' => null,
            'new' => $data['sent_data'][0]['delivery_method'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.delivery_cost'], [
            'old' => null,
            'new' => $data['sent_data'][0]['delivery_cost'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.tracking_number'], [
            'old' => null,
            'new' => $data['sent_data'][0]['tracking_number'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.date_sent'], [
            'old' => null,
            'new' => $now->format('Y-m-d'),
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.status'], [
            'old' => null,
            'new' => DeliveryStatus::Sent(),
            'type' => 'added',
        ]);

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_STATUS_CHANGED
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function fail_change_in_progress_to_sent_not_sent_data()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())->sales_manager($sales)->create();

        $data['status'] = OrderStatus::Sent->value;

        $this->assertTrue($model->status->isInProcess());

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg(
            $res,
            __('validation.required', ['attribute' => 'sent data']),
            'sent_data'
        );
    }

    /** @test */
    public function fail_change_in_progress_to_sent_sent_data_more_than_two()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())->sales_manager($sales)->create();

        $now = CarbonImmutable::now();

        $data['status'] = OrderStatus::Sent->value;
        $data['sent_data'] = [
            [
                'delivery_method' => DeliveryMethod::Our_delivery(),
                'delivery_cost' => 2.9,
                'date_sent' => $now->format('Y-m-d'),
                'tracking_number' => '67686',
            ],
            [
                'delivery_method' => DeliveryMethod::Our_delivery(),
                'delivery_cost' => 2.9,
                'date_sent' => $now->format('Y-m-d'),
                'tracking_number' => '67686',
            ],
            [
                'delivery_method' => DeliveryMethod::Our_delivery(),
                'delivery_cost' => 2.9,
                'date_sent' => $now->format('Y-m-d'),
                'tracking_number' => '67686',
            ]
        ];

        $this->assertTrue($model->status->isInProcess());

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg(
            $res,
            __('validation.max.array', ['attribute' => 'sent data', 'max' => 2]),
            'sent_data'
        );
    }

    /** @test */
    public function fail_change_in_progress_to_sent_terms_Immediately_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::In_process())
            ->sales_manager($sales)
            ->payment_terms(PaymentTerms::Immediately())
            ->is_paid(false)
            ->create();

        $data['status'] = OrderStatus::Sent->value;
        $data['sent_data'] = [
            [
                'delivery_method' => DeliveryMethod::Our_delivery(),
                'delivery_cost' => 2.9,
                'date_sent' => CarbonImmutable::now()->format('Y-m-d'),
                'tracking_number' => '67686',
            ]
        ];

        $this->assertTrue($model->status->isInProcess());

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg(
            $res,
            __('exceptions.orders.status_cant_be_change'),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /** @test */
    public function success_change_in_progress_to_canceled()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())->sales_manager($sales)->create();

        $data['status'] = OrderStatus::Canceled->value;

        $this->assertTrue($model->status->isInProcess());

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Canceled(),
                ],
            ])
        ;
    }

    /** @test */
    public function success_change_in_progress_to_pending_pickup()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::In_process())
            ->sales_manager($sales)
            ->delivery_type(DeliveryType::Pickup)
            ->create();

        $data['status'] = OrderStatus::Pending_pickup->value;

        $this->assertTrue($model->status->isInProcess());

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Pending_pickup(),
                ],
            ])
        ;
    }

    /** @test */
    public function fail_change_in_progress_to_pending_pickup_shipping_method_not_pickup()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())
            ->delivery_type(DeliveryType::Delivery)
            ->sales_manager($sales)->create();

        $data['status'] = OrderStatus::Pending_pickup->value;

        $this->assertTrue($model->status->isInProcess());

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg(
            $res,
            __('exceptions.orders.if_status_change_shipping_method_must_be_pickup'),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * @dataProvider validateInProgressStatus
     * @test
     */
    public function validate_in_progress_data($status)
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())->sales_manager($sales)->create();

        $this->assertTrue($model->status->isInProcess());

        $data['status'] = $status;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __('exceptions.orders.status_cant_be_change'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function validateInProgressStatus(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Delivered()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /** @test */
    public function change_pending_pickup_to_delivered_check_transaction_finished()
    {
        $user = $this->loginUserAsSuperAdmin();

        $wasQty_1 = 20;
        $wasQty_2 = 10;
        $reserveQty_1 = 10;
        $reserveQty_2 = 1;

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity($wasQty_1)->create();
        $inventory_2 = $this->inventoryBuilder->quantity($wasQty_2)->create();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->status(OrderStatus::Pending_pickup())
            ->create();

        $item_1 = $this->itemBuilder->order($model)
            ->qty($reserveQty_1)->inventory($inventory_1)->create();
        $item_2 = $this->itemBuilder->order($model)
            ->qty($reserveQty_2)->inventory($inventory_2)->create();

        /** @var $transaction_1 Transaction */
        $transaction_1 = $this->transactionBuilder
            ->inventory($inventory_1)
            ->order($model)
            ->price($inventory_1->price_retail)
            ->qty($reserveQty_1)
            ->is_reserve(true)
            ->create();
        $transaction_id_1 = $transaction_1->id;

        /** @var $transaction_2 Transaction */
        $transaction_2 = $this->transactionBuilder
            ->inventory($inventory_2)
            ->order($model)
            ->price($inventory_2->price_retail)
            ->qty($reserveQty_2)
            ->is_reserve(true)
            ->create();
        $transaction_id_2 = $transaction_2->id;

        $data['status'] = OrderStatus::Delivered();

        $this->assertTrue($model->status->isPendingPickup());
        $this->assertNull($model->status_changed_at);
        $this->assertEmpty($inventory_1->histories);
        $this->assertEmpty($inventory_2->histories);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered->value,
                ],
            ])
        ;

        $this->assertNull(Transaction::find($transaction_id_1));
        $this->assertNull(Transaction::find($transaction_id_2));

        /** @var $new_transaction_1 Transaction */
        $new_transaction_1 = Transaction::query()->where('order_parts_id', $model->id)->where('inventory_id', $inventory_1->id)->first();

        $this->assertTrue($new_transaction_1->operation_type->isSold());
        $this->assertFalse($new_transaction_1->is_reserve);
        $this->assertEquals($new_transaction_1->price, $inventory_1->price_retail);
        $this->assertEquals($new_transaction_1->quantity, $reserveQty_1);

        /** @var $new_transaction_2 Transaction */
        $new_transaction_2 = Transaction::query()->where('order_parts_id', $model->id)->where('inventory_id', $inventory_2->id)->first();
        $this->assertTrue($new_transaction_2->operation_type->isSold());
        $this->assertFalse($new_transaction_2->is_reserve);
        $this->assertEquals($new_transaction_2->price, $inventory_2->price_retail);
        $this->assertEquals($new_transaction_2->quantity, $reserveQty_2);

        $model->refresh();

        $this->assertNotNull($model->status_changed_at);

        $inventory_1->refresh();
        $inventory_2->refresh();

        $history_1 = $inventory_1->histories[0];
        $this->assertEquals($history_1->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_1->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_1->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_1->details);

        $history_2 = $inventory_2->histories[0];

        $this->assertEquals($history_2->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_2->user_id, $user->id);
        $this->assertEquals($history_2->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_2->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_2->stock_number,
            'inventory_name' => $inventory_2->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_2->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_2->details);
    }

    /**
     * @dataProvider validateInProgressStatus
     * @test
     */
    public function validate_pending_pickup_data($status)
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::In_process())->sales_manager($sales)->create();

        $this->assertTrue($model->status->isInProcess());

        $data['status'] = $status;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __('exceptions.orders.status_cant_be_change'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function validatePendingPickupStatus(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /** @test */
    public function success_change_sent_to_delivered_payment_terms_15_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Sent())
            ->is_paid(false)
            ->payment_terms(PaymentTerms::Day_15())
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Delivered->value;

        $this->assertTrue($model->status->isSent());
        $this->assertNull($model->past_due_at);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered(),
                ],
            ])
        ;

        $model->refresh();

        $this->assertEquals(
            $model->past_due_at->timestamp,
            $now->addHours(config('orders.parts.over_due.'.PaymentTerms::Day_15()))->timestamp
        );
    }

    /** @test */
    public function success_change_sent_to_delivered_payment_terms_30_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Sent())
            ->is_paid(false)
            ->payment_terms(PaymentTerms::Day_30())
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Delivered->value;

        $this->assertTrue($model->status->isSent());
        $this->assertNull($model->past_due_at);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered(),
                ],
            ])
        ;

        $model->refresh();

        $this->assertEquals(
            $model->past_due_at->timestamp,
            $now->addHours(config('orders.parts.over_due.'.PaymentTerms::Day_30()))->timestamp
        );
    }

    /** @test */
    public function success_change_sent_to_delivered_payment_terms_30_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Sent())
            ->is_paid(true)
            ->payment_terms(PaymentTerms::Day_30())
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Delivered->value;

        $this->assertTrue($model->status->isSent());
        $this->assertNull($model->past_due_at);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered(),
                ],
            ])
        ;

        $model->refresh();

        $this->assertEquals(
            $model->past_due_at->timestamp,
            $now->addHours(config('orders.parts.over_due.'.PaymentTerms::Day_30()))->timestamp
        );
    }

    /** @test */
    public function success_change_sent_to_delivered_payment_terms_immediately()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Sent())
            ->is_paid(true)
            ->payment_terms(PaymentTerms::Immediately())
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Delivered->value;

        $this->assertTrue($model->status->isSent());
        $this->assertNull($model->past_due_at);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered(),
                ],
            ])
        ;

        $model->refresh();

        $this->assertNull($model->past_due_at);
    }

    /** @test */
    public function success_change_sent_to_delivered_check_delivery_status()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->status(OrderStatus::Sent())
            ->create();

        $delivery_1 = $this->deliveryBuilder->order($model)->status(DeliveryStatus::Sent)->create();
        $delivery_2 = $this->deliveryBuilder->order($model)->status(DeliveryStatus::Sent)->create();

        $data['status'] = OrderStatus::Delivered();

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered(),
                ],
            ])
        ;

        $delivery_1->refresh();
        $delivery_2->refresh();

        $this->assertTrue($delivery_1->status->isDelivered());
        $this->assertTrue($delivery_2->status->isDelivered());
    }

    /** @test */
    public function success_change_sent_to_lost()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Sent())
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Lost->value;

        $this->assertTrue($model->status->isSent());

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Lost(),
                ],
            ])
        ;
    }

    /** @test */
    public function change_to_lost_check_transaction_finished()
    {
        $user = $this->loginUserAsSuperAdmin();

        $wasQty_1 = 20;
        $wasQty_2 = 10;
        $reserveQty_1 = 10;
        $reserveQty_2 = 1;

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity($wasQty_1)->create();
        $inventory_2 = $this->inventoryBuilder->quantity($wasQty_2)->create();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->status(OrderStatus::Sent())
            ->create();

        $item_1 = $this->itemBuilder->order($model)
            ->qty($reserveQty_1)->inventory($inventory_1)->create();
        $item_2 = $this->itemBuilder->order($model)
            ->qty($reserveQty_2)->inventory($inventory_2)->create();

        /** @var $transaction_1 Transaction */
        $transaction_1 = $this->transactionBuilder
            ->inventory($inventory_1)
            ->order($model)
            ->price($inventory_1->price_retail)
            ->qty($reserveQty_1)
            ->is_reserve(true)
            ->create();
        $transaction_id_1 = $transaction_1->id;

        /** @var $transaction_2 Transaction */
        $transaction_2 = $this->transactionBuilder
            ->inventory($inventory_2)
            ->order($model)
            ->price($inventory_2->price_retail)
            ->qty($reserveQty_2)
            ->is_reserve(true)
            ->create();
        $transaction_id_2 = $transaction_2->id;

        $data['status'] = OrderStatus::Lost();

        $this->assertTrue($model->status->isSent());
        $this->assertNull($model->status_changed_at);
        $this->assertEmpty($inventory_1->histories);
        $this->assertEmpty($inventory_2->histories);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Lost->value,
                ],
            ])
        ;

        $this->assertNull(Transaction::find($transaction_id_1));
        $this->assertNull(Transaction::find($transaction_id_2));

        /** @var $new_transaction_1 Transaction */
        $new_transaction_1 = Transaction::query()->where('order_parts_id', $model->id)->where('inventory_id', $inventory_1->id)->first();

        $this->assertTrue($new_transaction_1->operation_type->isSold());
        $this->assertFalse($new_transaction_1->is_reserve);
        $this->assertEquals($new_transaction_1->price, $inventory_1->price_retail);
        $this->assertEquals($new_transaction_1->quantity, $reserveQty_1);

        /** @var $new_transaction_2 Transaction */
        $new_transaction_2 = Transaction::query()->where('order_parts_id', $model->id)->where('inventory_id', $inventory_2->id)->first();
        $this->assertTrue($new_transaction_2->operation_type->isSold());
        $this->assertFalse($new_transaction_2->is_reserve);
        $this->assertEquals($new_transaction_2->price, $inventory_2->price_retail);
        $this->assertEquals($new_transaction_2->quantity, $reserveQty_2);

        $model->refresh();

        $this->assertNotNull($model->status_changed_at);

        $inventory_1->refresh();
        $inventory_2->refresh();

        $history_1 = $inventory_1->histories[0];
        $this->assertEquals($history_1->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_1->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_1->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_1->details);

        $history_2 = $inventory_2->histories[0];

        $this->assertEquals($history_2->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_2->user_id, $user->id);
        $this->assertEquals($history_2->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_2->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_2->stock_number,
            'inventory_name' => $inventory_2->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_2->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_2->details);
    }

    /** @test */
    public function success_change_sent_to_damaged()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Sent())
            ->sales_manager($sales)
            ->create();

        $delivery_1 = $this->deliveryBuilder->order($model)->status(DeliveryStatus::Sent)->create();
        $delivery_2 = $this->deliveryBuilder->order($model)->status(DeliveryStatus::Sent)->create();

        $data['status'] = OrderStatus::Damaged->value;

        $this->assertTrue($model->status->isSent());

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Damaged(),
                ],
            ])
        ;

        $delivery_1->refresh();
        $delivery_2->refresh();

        $this->assertTrue($delivery_1->status->isDelivered());
        $this->assertTrue($delivery_2->status->isDelivered());

    }

    /** @test */
    public function change_to_damaged_check_transaction_finished()
    {
        $user = $this->loginUserAsSuperAdmin();

        $wasQty_1 = 20;
        $wasQty_2 = 10;
        $reserveQty_1 = 10;
        $reserveQty_2 = 1;

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity($wasQty_1)->create();
        $inventory_2 = $this->inventoryBuilder->quantity($wasQty_2)->create();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->status(OrderStatus::Sent())
            ->create();

        $item_1 = $this->itemBuilder->order($model)
            ->qty($reserveQty_1)->inventory($inventory_1)->create();
        $item_2 = $this->itemBuilder->order($model)
            ->qty($reserveQty_2)->inventory($inventory_2)->create();

        /** @var $transaction_1 Transaction */
        $transaction_1 = $this->transactionBuilder
            ->inventory($inventory_1)
            ->order($model)
            ->price($inventory_1->price_retail)
            ->qty($reserveQty_1)
            ->is_reserve(true)
            ->create();
        $transaction_id_1 = $transaction_1->id;

        /** @var $transaction_2 Transaction */
        $transaction_2 = $this->transactionBuilder
            ->inventory($inventory_2)
            ->order($model)
            ->price($inventory_2->price_retail)
            ->qty($reserveQty_2)
            ->is_reserve(true)
            ->create();
        $transaction_id_2 = $transaction_2->id;

        $data['status'] = OrderStatus::Damaged();

        $this->assertNull($model->status_changed_at);
        $this->assertEmpty($inventory_1->histories);
        $this->assertEmpty($inventory_2->histories);

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Damaged->value,
                ],
            ])
        ;

        $this->assertNull(Transaction::find($transaction_id_1));
        $this->assertNull(Transaction::find($transaction_id_2));

        /** @var $new_transaction_1 Transaction */
        $new_transaction_1 = Transaction::query()->where('order_parts_id', $model->id)->where('inventory_id', $inventory_1->id)->first();

        $this->assertTrue($new_transaction_1->operation_type->isSold());
        $this->assertFalse($new_transaction_1->is_reserve);
        $this->assertEquals($new_transaction_1->price, $inventory_1->price_retail);
        $this->assertEquals($new_transaction_1->quantity, $reserveQty_1);

        /** @var $new_transaction_2 Transaction */
        $new_transaction_2 = Transaction::query()->where('order_parts_id', $model->id)->where('inventory_id', $inventory_2->id)->first();
        $this->assertTrue($new_transaction_2->operation_type->isSold());
        $this->assertFalse($new_transaction_2->is_reserve);
        $this->assertEquals($new_transaction_2->price, $inventory_2->price_retail);
        $this->assertEquals($new_transaction_2->quantity, $reserveQty_2);

        $model->refresh();

        $this->assertNotNull($model->status_changed_at);

        $inventory_1->refresh();
        $inventory_2->refresh();

        $history_1 = $inventory_1->histories[0];
        $this->assertEquals($history_1->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_1->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_1->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_1->details);

        $history_2 = $inventory_2->histories[0];

        $this->assertEquals($history_2->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_2->user_id, $user->id);
        $this->assertEquals($history_2->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_2->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_2->stock_number,
            'inventory_name' => $inventory_2->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_2->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_2->details);
    }

    /**
     * @dataProvider validateSentStatus
     * @test
     */
    public function validate_sent_data($status)
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Sent())->sales_manager($sales)->create();

        $this->assertTrue($model->status->isSent());

        $data['status'] = $status;
        $data['sent_data'] = [
            [
                'delivery_method' => DeliveryMethod::Our_delivery(),
                'delivery_cost' => 2.9,
                'date_sent' =>CarbonImmutable::now()->format('Y-m-d'),
                'tracking_number' => '67686',
            ]
        ];

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __('exceptions.orders.status_cant_be_change'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function validateSentStatus(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
        ];
    }

    /** @test */
    public function success_change_delivered_to_returned()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $days = config('orders.parts.change_status_delivered_to_returned') - 1;

        $date = CarbonImmutable::now()->subDays($days);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Delivered(), $date)
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Returned->value;

        $this->assertTrue($model->status->isDelivered());

        $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Returned(),
                ],
            ])
        ;
    }

    /** @test */
    public function fail_change_delivered_to_returned_expired()
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        $days = config('orders.parts.change_status_delivered_to_returned') + 1;
        $date = CarbonImmutable::now()->subDays($days);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Delivered(), $date)
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Returned->value;

        $this->assertTrue($model->status->isDelivered());

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __('exceptions.orders.status_cant_be_change'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['status', null, 'validation.required', ['attribute' => 'validation.attributes.status']],
            ['status', 'wrong', 'validation.in', ['attribute' => 'validation.attributes.status']],
        ];
    }

    /**
     * @dataProvider statusCantChangeStatus
     * @test
     */
    public function fail_not_support_change_status($status)
    {
        $this->loginUserAsSuperAdmin();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status($status)
            ->sales_manager($sales)
            ->create();

        $data['status'] = OrderStatus::Damaged->value;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __('exceptions.orders.status_cant_be_change'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function statusCantChangeStatus(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /** @test */
    public function fail_not_sales_manager()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data['status'] = OrderStatus::In_process->value;

        $this->assertTrue($model->status->isNew());
        $this->assertNull($model->sales_manager_id);

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_switch_status_not_sales"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id + 1]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.parts.change-status', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
