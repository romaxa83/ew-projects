<?php

namespace Tests\Unit\Models\Orders\Parts\Order;

use App\Enums\Orders\Parts\DeliveryType;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GetAmountTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_total_max_summ()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_cost(2)
            ->create();

        $this->itemBuilder
            ->order($model)
            ->price(10)
            ->qty(2)
            ->delivery_cost(3)
            ->create();
        $this->itemBuilder
            ->order($model)
            ->price(15)
            ->qty(7)
            ->delivery_cost(0)
            ->create();

        $tax = 0;
        $totalDelivery = $model->delivery_cost;
        $totalItem = ((10+3)*2) + ((15+0)*7);

        $this->assertEquals(
            $model->getAmount(),
            round($totalDelivery + $tax + $totalItem, 2)
        );
    }

    /** @test */
    public function get_total_min_summ()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_cost(2)
            ->delivery_type(DeliveryType::Delivery)
            ->create();

        $this->itemBuilder
            ->order($model)
            ->price(10)
            ->qty(2)
            ->delivery_cost(3)
            ->create();
        $this->itemBuilder
            ->order($model)
            ->price(5)
            ->qty(7)
            ->delivery_cost(0)
            ->create();

        $tax = 0;
        $totalDelivery = $model->delivery_cost;
        $totalItem = ((10+3+3)*2) + ((5+0+0)*7);

        $this->assertEquals(
            $model->getAmount(),
            round($totalDelivery + $tax + $totalItem, 2)
        );
    }

    /** @test */
    public function get_total_min_summ_if_zero_delivery_cost()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_cost(0)
            ->delivery_type(DeliveryType::Delivery)
            ->create();

        $this->itemBuilder
            ->order($model)
            ->price(10)
            ->qty(2)
            ->delivery_cost(3)
            ->create();
        $this->itemBuilder
            ->order($model)
            ->price(5)
            ->qty(7)
            ->delivery_cost(0)
            ->create();

        $tax = 0;
        $totalDelivery = $model->delivery_cost;
        $totalItem = ((10+3+3)*2) + ((5+0+0)*7);

        $this->assertEquals(
            $model->getAmount(),
            round($totalDelivery + $tax + $totalItem, 2)
        );
    }
}
