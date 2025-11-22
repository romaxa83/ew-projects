<?php

namespace Feature\Http\Api\V1\Orders\BS\Payment;

use App\Enums\Orders\PaymentMethod;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\PaymentBuilder;
use Tests\TestCase;

class AddTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected PaymentBuilder $orderPaymentBuilder;
    protected OrderTypeOfWorkBuilder $orderTypeOfWorkBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderPaymentBuilder = resolve(PaymentBuilder::class);
        $this->orderTypeOfWorkBuilder = resolve(OrderTypeOfWorkBuilder::class);

        $this->data = [
            'amount' => 2,
            'payment_date' => CarbonImmutable::now()->format('m/d/Y'),
            'payment_method' => PaymentMethod::CashApp->value,
            'notes' => 'text area',
            'reference_number' => "OP78999",
        ];
    }

    /** @test */
    public function success_add()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['payment_date'] = $now->format('m/d/Y');

        $this->assertEmpty($model->payments);
        $this->assertFalse($model->is_paid);
        $this->assertNull($model->paid_at);

        $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                ]
            ])
            ->assertJson([
                'data' => [
                    'amount' => $data['amount'],
                    'payment_date' => $now->startOfDay()->timestamp,
                    'payment_method' => PaymentMethod::CashApp->value,
                    'payment_method_name' => PaymentMethod::CashApp->label(),
                    'notes' => $data['notes'],
                    'reference_number' => $data['reference_number'],
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->total_amount);
        $this->assertEquals($model->paid_amount, $data['amount']);
        $this->assertEquals($model->debt_amount,round($model->total_amount - $data['amount'],2));
    }

    /** @test */
    public function success_add_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['payment_date'] = $now->format('m/d/Y');

        $id = $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data)
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
        $this->assertEquals($history->details['payments.'.$id.'.reference_number'], [
            'old' => null,
            'new' => $data['reference_number'],
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

        $res = $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data, [
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

        $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data, [
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

        $res = $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['amount', null, 'validation.required', ['attribute' => 'validation.attributes.amount']],
            ['amount', 20, 'validation.max.numeric', [
                'attribute' => 'validation.attributes.amount',
                'max' => "10.00",
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
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => 999999]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.payment.add', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
