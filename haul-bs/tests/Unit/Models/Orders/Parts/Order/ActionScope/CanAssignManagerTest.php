<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanAssignManagerTest extends TestCase
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

        $this->assertTrue($model->canAssignManger());
    }

    /**
     * @dataProvider statusesSuccess
     * @test
     */
    public function fail_order_is_draft(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->status($status)->create();

        $this->assertFalse($model->canAssignManger());
    }

    public static function statusesSuccess(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }
}
