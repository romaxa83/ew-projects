<?php

namespace WezomCms\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Models\OrderItem;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        return [
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween(1, 10),
            'price' => $product->cost,
            'purchase_price' => $product->cost,
        ];
    }
}
