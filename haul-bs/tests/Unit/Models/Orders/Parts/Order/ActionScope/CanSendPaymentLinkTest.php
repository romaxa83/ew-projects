<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanSendPaymentLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_can()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)->create();

        $this->assertFalse($model->isDraft());
        $this->assertFalse($model->isPaid());
        $this->assertTrue($model->canSendPaymentLink());
    }

    /** @test */
    public function fail_order_is_draft()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $this->assertTrue($model->isDraft());
        $this->assertFalse($model->canSendPaymentLink());
    }

    /** @test */
    public function fail_order_is_paid()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        $this->assertTrue($model->isPaid());
        $this->assertFalse($model->canSendPaymentLink());
    }
}
