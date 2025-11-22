<?php

namespace Database\Factories\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\PackingSlipItem;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|PackingSlipItem[]|PackingSlipItem create(array $attributes = [])
 */
class PackingSlipItemFactory extends BaseFactory
{
    protected $model = PackingSlipItem::class;

    public function definition(): array
    {
        $qty = random_int(10, 30);
        return [
            'packing_slip_id' => PackingSlipItem::factory(),
            'product_id' => Product::factory(),
            'order_item_id' => Item::factory(),
            'qty' => $qty,
            'description' => $this->faker->sentence
        ];
    }
}
