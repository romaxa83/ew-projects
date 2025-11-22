<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanSendInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /**
     * @dataProvider statuses
     * @test
     */
    public function success_can()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::New())
            ->create();

        $this->assertTrue($model->canSendInvoice());
    }

    /** @test */
    public function fail_order_is_draft()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::New())
            ->draft(true)
            ->create();

        $this->assertTrue($model->isDraft());
        $this->assertFalse($model->canSendInvoice());
    }

    /** @test */
    public function fail_order_is_canceled()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Canceled())
            ->create();

        $this->assertFalse($model->isDraft());
        $this->assertFalse($model->canSendInvoice());
    }

    public static function statuses(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }
}

