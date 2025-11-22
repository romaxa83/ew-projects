<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{

    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'price' => $this->faker->numberBetween(50, 10000),
        ];
    }
}
