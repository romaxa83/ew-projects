<?php

namespace Tests\Unit\Models\Orders\Parts\Order;

use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GetSavingTotalTest extends TestCase
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
    public function get_saving_total()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder
            ->order($model)
            ->price(10)
            ->price_old(18)
            ->qty(2)
            ->create();
        $this->itemBuilder
            ->order($model)
            ->price(15)
            ->price_old(18)
            ->qty(7)
            ->create();

        $this->assertEquals(37, $model->getSavingAmount());
    }

    /** @test */
    public function get_saving_total_with_delivery_cost()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder->order($model)
            ->price(10)
            ->price_old(18)
            ->delivery_cost(5)
            ->qty(2)
            ->create();
        $this->itemBuilder->order($model)
            ->price(15)
            ->price_old(18)
            ->qty(7)
            ->create();

        $this->assertEquals(27, $model->getSavingAmount());
    }

    /** @test */
    public function get_saving_total_more()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder->order($model)->price_old(10)->price(8)->qty(2)->create();
        $this->itemBuilder->order($model)->price_old(15)->price(13.5)->qty(7)->create();

        $this->assertEquals(14.5, $model->getSavingAmount());
    }

    /** @test */
    public function get_saving_total_more_negative()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder->order($model)->price(10)->price_old(12)->qty(2)->create();
        $this->itemBuilder->order($model)->price(15)->price_old(13.5)->qty(7)->create();

        $this->assertEquals(-6.5, $model->getSavingAmount());
    }
}
