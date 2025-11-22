<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Bonus;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusFactory extends Factory
{

    protected $model = Bonus::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'price' => $this->faker->numberBetween(50, 10000),
        ];
    }
}
