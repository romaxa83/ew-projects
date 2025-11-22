<?php

namespace Tests\Unit\Models\Orders\Parts\Order;

use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GetSubtotalTest extends TestCase
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
    public function get_subtotal()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder->order($model)
            ->price(10)
            ->price_old(18)
            ->qty(2)->create();
        $this->itemBuilder->order($model)
            ->price(15)
            ->qty(7)
            ->create();

        $this->assertEquals($model->getSubtotal(), (18 * 2) + (15 * 7));
    }
}

