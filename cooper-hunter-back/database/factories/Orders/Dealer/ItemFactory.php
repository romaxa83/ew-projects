<?php

namespace Database\Factories\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Item[]|Item create(array $attributes = [])
 */
class ItemFactory extends BaseFactory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $price = random_int(1000, 3000);
        $qty = random_int(1, 10);
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'price' => $price,
            'qty' => $qty,
            'discount' => random_int(0, 100),
            'discount_total' => random_int(0, 100),
            'total' => $price * $qty,
            'description' => $this->faker->sentence
        ];
    }
}
