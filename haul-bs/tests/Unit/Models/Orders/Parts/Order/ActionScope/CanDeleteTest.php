<?php

namespace Tests\Unit\Models\Orders\Parts\Order\ActionScope;

use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class CanDeleteTest extends TestCase
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
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::New())
            ->create();

        $this->assertFalse($model->isPaid());
        $this->assertTrue($model->canDelete());
    }

    /** @test */
    public function fail_can_auth_user_as_admin()
    {
        $this->loginUserAsAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::New())
            ->create();

        $this->assertFalse($model->isPaid());
        $this->assertFalse($model->canDelete());
    }

    /** @test */
    public function fail_can_auth_user_as_sales_manager()
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::New())
            ->create();

        $this->assertFalse($model->isPaid());
        $this->assertFalse($model->canDelete());
    }

    /** @test */
    public function fail_order_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::New())
            ->is_paid(true)
            ->create();

        $this->assertTrue($model->isPaid());
        $this->assertFalse($model->canDelete());
    }

    /**
     * @dataProvider statuses
     * @test
     */
    public function fail_order_wrong_status_as_not_paid(string $status)
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status($status)
            ->create();

        $this->assertFalse($model->isPaid());
        $this->assertFalse($model->canDelete());
    }

    /**
     * @dataProvider statuses
     * @test
     */
    public function fail_order_wrong_status_as_paid(string $status)
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status($status)
            ->is_paid(true)
            ->create();

        $this->assertTrue($model->isPaid());
        $this->assertFalse($model->canDelete());
    }

    public static function statuses(): array
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

