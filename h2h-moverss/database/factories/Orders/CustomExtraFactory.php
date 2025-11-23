<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class CustomExtraFactory extends BaseFactory
{
    protected $model = Order\CustomExtra::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'title' => $this->faker->city(),
            'price' => 2.5,
        ];
    }
}
