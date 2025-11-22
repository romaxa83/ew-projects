<?php

namespace Tests\Unit\Models\Orders\Parts\Order;

use App\Enums\Orders\Parts\DeliveryType;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GetTotalDeliveryTest extends TestCase
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
    public function get_total_min_summ()
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

        $this->assertEquals($model->delivery_cost, $model->getTotalDelivery());
    }

    /** @test */
    public function get_total_max_summ()
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

        $this->assertEquals(((3*2) + (0*7)) + 2 ,  $model->getTotalDelivery());
    }
}
