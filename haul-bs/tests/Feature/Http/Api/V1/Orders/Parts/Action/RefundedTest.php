<?php

namespace Feature\Http\Api\V1\Orders\Parts\Action;

use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class RefundedTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_refunded()
    {
        Event::fake([RequestToEcom::class]);

        $user = $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)
            ->source(OrderSource::Haulk_Depot)
            ->status(OrderStatus::Canceled())
            ->create();

        $this->assertFalse($model->isRefunded());

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.refunded', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'is_refunded' => true,
                ],
            ])
        ;

        $model->refresh();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.refunded');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['refunded_at'], [
            'old' => null,
            'new' => $now->format('Y-m-d H:i'),
            'type' => 'added',
        ]);


        $this->assertEquals(1, count($history->details));

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_REFUNDED
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function fail_refunded_order_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)
            ->status(OrderStatus::Canceled())
            ->create();

        $this->assertFalse($model->isRefunded());

        $res = $this->postJson(route('api.v1.orders.parts.refunded', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.must_be_paid'), Response::HTTP_BAD_REQUEST);
    }


    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($status)
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->status($status)->create();

        $res = $this->postJson(route('api.v1.orders.parts.refunded', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.cant_change_refunded'), Response::HTTP_BAD_REQUEST);
    }

    public static function validate(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Delivered()],
            [OrderStatus::Pending_pickup()],
        ];
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.refunded', ['id' => $model->id + 1]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.refunded', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.refunded', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
