<?php

namespace Tests\Unit\Models\Orders\Parts\Item;

use App\Models\Orders\Parts\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\TestCase;

class GetPriceTest extends TestCase
{
    use DatabaseTransactions;

    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        $this->itemBuilder = resolve(ItemBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_price()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price(8.5)
            ->delivery_cost(2)
            ->discount(0)
            ->create();

        $this->assertEquals(8.5 + 2, $model->getPrice());
    }

    /** @test */
    public function get_price_not_delivery_cost()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price(8.5)
            ->delivery_cost(0)
            ->discount(0)
            ->create();

        $this->assertEquals(8.5, $model->getPrice());
    }

    /** @test */
    public function get_price_with_discount()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price(8.5)
            ->delivery_cost(2)
            ->discount(3)
            ->create();

        $this->assertEquals(price_with_discount(8.5, 3)
            + price_with_discount(2, 3),
            $model->getPrice());
    }

    /** @test */
    public function get_price_with_discount_not_delivery_cost()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price(8.5)
            ->delivery_cost(0)
            ->discount(3)
            ->create();

        $this->assertEquals(price_with_discount(8.5, 3),
            $model->getPrice());
    }
}
