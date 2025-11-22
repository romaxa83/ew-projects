<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanChangeStatusTest extends TestCase
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
        $model = $this->orderBuilder->status($status)->create();

        $this->assertTrue($model->canChangeStatus());
    }

    /**
     * @dataProvider statusesSuccess
     * @test
     */
    public function fail_as_draft(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->draft(true)->create();

        $this->assertTrue($model->isDraft());
        $this->assertFalse($model->canChangeStatus());
    }

    public static function statusesSuccess(): array
    {
        return [
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
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

        $this->assertFalse($model->canChangeStatus());
    }

    public static function statusesFail(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }
}
