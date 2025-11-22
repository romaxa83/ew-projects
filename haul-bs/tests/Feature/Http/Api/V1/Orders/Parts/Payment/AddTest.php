<?php

namespace Feature\Http\Api\V1\Orders\Parts\Payment;

use App\Enums\Orders\Parts\PaymentMethod;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\PaymentBuilder;
use Tests\TestCase;

class AddTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected PaymentBuilder $paymentBuilder;
    protected TransactionBuilder $transactionBuilder;
    protected InventoryBuilder $inventoryBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);

        $this->data = [
            'amount' => 2,
            'payment_date' => CarbonImmutable::now()->format('m/d/Y'),
            'payment_method' => PaymentMethod::Online(),
            'notes' => 'text area',
        ];
    }

    /** @test */
    public function success_add_but_not_paid()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $total = 10;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->total_amount($total)
            ->paid_amount(0)
            ->debt_amount($total)
            ->create();
        $this->itemBuilder->order($model)->qty(2)->price(5)->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['payment_date'] = $now->format('m/d/Y');

        $this->assertEmpty($model->payments);
        $this->assertFalse($model->is_paid);

        $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                ]
            ])
            ->assertJson([
                'data' => [
                    'amount' => $data['amount'],
                    'payment_date' => $now->startOfDay()->timestamp,
                    'payment_method' => PaymentMethod::Online->value,
                    'payment_method_name' => PaymentMethod::Online->label(),
                    'notes' => $data['notes'],
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->payments);
        $this->assertFalse($model->is_paid);

        $this->assertEquals($model->total_amount, $total);
        $this->assertEquals($model->paid_amount, $data['amount']);
        $this->assertEquals($model->debt_amount, $total - $data['amount']);

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_add_and_paid()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $total = 10;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->total_amount($total)
            ->paid_amount(0)
            ->debt_amount($total)
            ->create();
        $this->itemBuilder->order($model)->qty(2)->price(5)->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['amount'] = $total;
        $data['payment_date'] = $now->format('m/d/Y');

        $this->assertEmpty($model->payments);
        $this->assertFalse($model->is_paid);

        $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                ]
            ])
            ->assertJson([
                'data' => [
                    'amount' => $data['amount'],
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->payments);
        $this->assertTrue($model->is_paid);

        $this->assertEquals($model->total_amount, $total);
        $this->assertEquals($model->paid_amount, $data['amount']);
        $this->assertEquals($model->debt_amount, $total - $data['amount']);

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_IS_PAID
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

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();
        $this->itemBuilder->order($model)->qty(2)->price(5)->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['payment_date'] = $now->format('m/d/Y');

        $id = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data)
            ->json('data.id')
        ;

        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.created_payment');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['payments.'.$id.'.amount'], [
            'old' => null,
            'new' => $data['amount'],
            'type' => 'added',
        ]);

        $this->assertEquals($history->details['payments.'.$id.'.payment_method'], [
            'old' => null,
            'new' => $data['payment_method'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['payments.'.$id.'.notes'], [
            'old' => null,
            'new' => $data['notes'],
            'type' => 'added',
        ]);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;
        $data['amount'] = null;

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.amount')]),
            'amount'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;

        $this->assertEmpty($model->payments);

        $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertEmpty($model->payments);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['amount', null, 'validation.required', ['attribute' => 'validation.attributes.amount']],
            ['amount', 20, 'validation.max.numeric', [
                'attribute' => 'validation.attributes.amount',
                'max' => "10",
            ]],
            ['payment_date', null, 'validation.required', ['attribute' => 'validation.attributes.payment_date']],
            ['payment_date', 'Y-m-d', 'validation.date_format', [
                'attribute' => 'validation.attributes.payment_date',
                'format' => 'm/d/Y',
            ]],
            ['payment_method', null, 'validation.required', ['attribute' => 'validation.attributes.payment_method']],
            ['payment_method', 'wrong', 'validation.in', ['attribute' => 'validation.attributes.payment_method']],
        ];
    }

    /** @test */
    public function fail_order_is_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->total_amount(10)
            ->paid_amount(0)
            ->debt_amount(10)
            ->draft(true)
            ->create();
        $this->itemBuilder->order($model)->qty(2)->price(5)->create();


        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['payment_date'] = $now->format('m/d/Y');

        $this->assertEmpty($model->payments);
        $this->assertFalse($model->is_paid);

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.cant_add_payment"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_order_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->total_amount(10)
            ->paid_amount(0)
            ->debt_amount(10)
            ->is_paid(true)
            ->create();
        $this->itemBuilder->order($model)->qty(2)->price(5)->create();


        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['payment_date'] = $now->format('m/d/Y');

        $this->assertEmpty($model->payments);
        $this->assertTrue($model->is_paid);

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.cant_add_payment"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => 999999]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.payment.add', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
