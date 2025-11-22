<?php

namespace Feature\Http\Api\V1\Orders\Parts\Payment;

use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\PaymentBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected PaymentBuilder $paymentBuilder;
    protected ItemBuilder $itemBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->total_amount(10)
            ->paid_amount(5)
            ->debt_amount(5)
            ->create();
        $this->itemBuilder->price(5)->qty(2)->order($model)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->paymentBuilder->order($model)->amount(5)->create();

        $payment_1_id = $payment_1->id;

        $this->assertFalse($model->isPaid());

        $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]))
            ->assertNoContent()
        ;

        $this->assertNull(Payment::find($payment_1_id));

        $model->refresh();

        $this->assertEquals($model->total_amount, 10);
        $this->assertEquals($model->paid_amount, 0);
        $this->assertEquals($model->debt_amount, 10);
        $this->assertFalse($model->isPaid());

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_delete_be_not_paid()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->is_paid(true)
            ->total_amount(10)
            ->paid_amount(10)
            ->debt_amount(0)
            ->create();
        $this->itemBuilder->price(5)->qty(2)->order($model)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->paymentBuilder->order($model)->amount(6)->create();
        $payment_2 = $this->paymentBuilder->order($model)->amount(4)->create();

        $this->assertTrue($model->isPaid());

        $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertFalse($model->isPaid());

        $this->assertEquals($model->total_amount, 10);
        $this->assertEquals($model->paid_amount, 4);
        $this->assertEquals($model->debt_amount, 6);

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

        /** @var $payment_1 Payment */
        $payment_1 = $this->paymentBuilder->order($model)->create();

        $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
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
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->paymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
            'id' => 9999999,
            'paymentId' => $payment_1->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_payment()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->paymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
            'id' => $model->id,
            'paymentId' => 99999999
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found_payment"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->debt_amount(10)->create();

        /** @var $payment_1 Payment */
        $payment_1 = $this->paymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
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
        $payment_1 = $this->paymentBuilder->order($model)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.payment.delete', [
            'id' => $model->id,
            'paymentId' => $payment_1->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
