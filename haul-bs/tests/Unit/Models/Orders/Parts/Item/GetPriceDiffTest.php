<?php

namespace Tests\Unit\Models\Orders\Parts\Item;

use App\Models\Orders\Parts\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\TestCase;

class GetPriceDiffTest extends TestCase
{
    use DatabaseTransactions;

    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        $this->itemBuilder = resolve(ItemBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_price_diff()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price_old(10)
            ->price(8.5)
            ->qty(2)
            ->create();

        $this->assertEquals(0, $model->discount);
        $this->assertEquals(3, $model->getPriceDiff());
    }

    /** @test */
    public function get_price_diff_not_old_price()
    {
        /** @var $model Item */
        $model = $this->itemBuilder->price(10)->create();

        $this->assertNull($model->price_old);
        $this->assertEquals(0, $model->discount);
        $this->assertEquals(0, $model->getPriceDiff());
    }

    /** @test */
    public function get_price_diff_as_negative()
    {
        /** @var $model Item */
        $model = $this->itemBuilder->price_old(10)->price(11)->qty(3)->create();

        $this->assertEquals(0, $model->discount);
        $this->assertEquals(-3, $model->getPriceDiff());
    }
}
