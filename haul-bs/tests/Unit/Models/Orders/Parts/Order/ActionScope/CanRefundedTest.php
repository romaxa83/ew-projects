<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanRefundedTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /**
     * @dataProvider statusesSuccess
     * @test
     */
    public function success_can(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->is_paid(true)->create();

        $this->assertFalse($model->isDraft());
        $this->assertTrue($model->canRefunded());
    }

    /**
     * @dataProvider statusesSuccess
     * @test
     */
    public function fail_not_paid(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->is_paid(false)->create();

        $this->assertFalse($model->isDraft());
        $this->assertFalse($model->canRefunded());
    }

    /**
     * @dataProvider statusesSuccess
     * @test
     */
    public function success_fail_is_draft(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->status($status)->create();

        $this->assertTrue($model->isDraft());
        $this->assertFalse($model->canRefunded());
    }

    public static function statusesSuccess(): array
    {
        return [
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /**
     * @dataProvider statusesFail
     * @test
     */
    public function fail_order_is_wrong_status(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->status($status)
            ->create();

        $this->assertFalse($model->canRefunded());
    }

    public static function statusesFail(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
        ];
    }
}
