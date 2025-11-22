<?php

namespace Tests\Feature\Http\Api\V1\Orders\Parts\Action;

use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CancelOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_canceled()
    {
        $user = $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $sales = $this->userBuilder->asSalesManager()->create();
        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->status(OrderStatus::New())
            ->create();

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.cancel', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Canceled(),
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
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            "status" => OrderStatus::Canceled()
        ]);

        $this->assertEquals($history->details['status'], [
            'old' => OrderStatus::New(),
            'new' => OrderStatus::Canceled(),
            'type' => 'updated',
        ]);

        $this->assertEquals(1, count($history->details));
    }

    /** @test */
    public function success_canceled_return_inventory()
    {
        $user = $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $sales = $this->userBuilder->asSalesManager()->create();
        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->status(OrderStatus::New())
            ->create();

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.cancel', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Canceled(),
                ],
            ])
        ;

        $model->refresh();


    }

    /**
     * @dataProvider statusCantCanceled
     * @test
     */
    public function fail_cant_be_canceled($status)
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status($status)
            ->create();

        $this->assertFalse($model->isRefunded());

        $res = $this->postJson(route('api.v1.orders.parts.cancel', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.status_cant_be_change"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function statusCantCanceled(): array
    {
        return [
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }


    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.cancel', ['id' => $model->id + 1]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.cancel', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.cancel', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
