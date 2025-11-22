<?php

namespace Feature\Http\Api\V1\Orders\BS\Payment;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\PaymentBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
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
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->orderPaymentBuilder->order($model)->create();
        $payment_1_id = $payment_1->id;

        $this->deleteJson(route('api.v1.orders.bs.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]))
            ->assertNoContent()
        ;

        $this->assertNull(Payment::find($payment_1_id));
    }

    /** @test */
    public function success_add_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->orderPaymentBuilder->order($model)->create();

        $this->deleteJson(route('api.v1.orders.bs.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]));

        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.deleted_payment');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['payments.'.$payment_1->id.'.amount'], [
            'old' => $payment_1->amount,
            'new' => null,
            'type' => 'removed',
        ]);
        $this->assertEquals($history->details['payments.'.$payment_1->id.'.payment_method'], [
            'old' => $payment_1->payment_method->value,
            'new' => null,
            'type' => 'removed',
        ]);
        $this->assertEquals($history->details['payments.'.$payment_1->id.'.notes'], [
            'old' => $payment_1->notes,
            'new' => null,
            'type' => 'removed',
        ]);
        $this->assertEquals($history->details['payments.'.$payment_1->id.'.reference_number'], [
            'old' =>$payment_1->reference_number,
            'new' => null,
            'type' => 'removed',
        ]);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->orderPaymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.payment.delete', [
            'id' => 9999999,
            'paymentId' => $payment_1->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_payment()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->orderPaymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.payment.delete', [
            'id' => $model->id,
            'paymentId' => 99999999
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found_payment"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->orderPaymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->orderPaymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
