<?php

namespace Tests\Unit\Models\Orders\Parts\Item;

use App\Models\Orders\Parts\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\TestCase;

class GetSubtotalTest extends TestCase
{
    use DatabaseTransactions;

    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        $this->itemBuilder = resolve(ItemBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_price_old_total()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price_old(10)
            ->price(8.5)
            ->qty(2)
            ->create();

        $this->assertEquals($model->subtotal(),10 * 2);
    }

    /** @test */
    public function get_total_not_old()
    {
        /** @var $model Item */
        $model = $this->itemBuilder
            ->price(8.5)
            ->qty(2)
            ->create();

        $this->assertEquals($model->subtotal(),8.5 * 2);
    }
}
