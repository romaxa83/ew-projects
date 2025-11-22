<?php

namespace Feature\Http\Api\V1\Orders\Parts\Payment;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\Parts\Order;
use App\Notifications\Orders\Parts\PaymentLink;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class SendLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    private function mockingPaymentService(): void
    {
        $mock = $this->createMock(PaymentService::class);
        $mock->expects($this->once())
            ->method('getPaymentLink')
            ->will($this->returnValue('http://localhost'));
        $this->app->instance(PaymentService::class, $mock);
    }

    /** @test */
    public function success_send_link()
    {
        $this->mockingPaymentService();

        Notification::fake();

        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id,
        ]))
            ->assertOk()
            ->assertJsonPath('data.message', 'Success')
        ;

        Notification::assertSentTo(new AnonymousNotifiable(), PaymentLink::class,
            function ($notification, $channels, $notifiable) use ($model) {
                return $notifiable->routes['mail'] == $model->customer->email
                    && $notification->name == $model->customer->full_name
                    && $notification->link == 'http://localhost'
                    ;
            }
        );

        $model->refresh();

        /** @var $model Order */
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::ACTIVITY);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.send_payment_link');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            "client_name" => $model->customer->full_name,
            "client_email" => $model->customer->email->getValue()
        ]);
        $this->assertEmpty($history->details);
    }

    /** @test */
    public function success_send_link_as_ecommerce_client()
    {
        $this->mockingPaymentService();

        Notification::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->customer(null)
            ->ecommerce_client()
            ->create();

        $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id,
        ]))
            ->assertOk()
            ->assertJsonPath('data.message', 'Success')
        ;

        Notification::assertSentTo(new AnonymousNotifiable(), PaymentLink::class,
            function ($notification, $channels, $notifiable) use ($model) {
                return $notifiable->routes['mail'] == $model->ecommerce_client->email
                    && $notification->name == $model->ecommerce_client->getFullNameAttribute()
                    && $notification->link == 'http://localhost'
                    ;
            }
        );
    }

    /** @test */
    public function fail_order_is_paid()
    {
        Notification::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id,
        ]))
        ;

        self::assertErrorMsg($res, __("Order is paid"), Response::HTTP_UNPROCESSABLE_ENTITY);

        Notification::assertNotSentTo(new AnonymousNotifiable(), PaymentLink::class);
    }

    /** @test */
    public function fail_order_is_draft()
    {
        Notification::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id,
        ]))
        ;

        self::assertErrorMsg($res, __("Order is paid"), Response::HTTP_UNPROCESSABLE_ENTITY);

        Notification::assertNotSentTo(new AnonymousNotifiable(), PaymentLink::class);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id + 1,
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id,
        ]))
        ;

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.payment.send-link', [
            'id' => $model->id,
        ]))
        ;

        self::assertUnauthenticatedMessage($res);
    }
}
