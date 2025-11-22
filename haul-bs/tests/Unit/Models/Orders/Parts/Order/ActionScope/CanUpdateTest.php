<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanUpdateTest extends TestCase
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

        $this->assertTrue($model->canUpdate());
    }

    public static function statusesSuccess(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
        ];
    }

    /**
     * @dataProvider statusesFail
     * @test
     */
    public function fail_order_is_wrong_status(string $status)
    {
        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->create();

        $this->assertFalse($model->canUpdate());
    }

    public static function statusesFail(): array
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
}
