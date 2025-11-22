<?php

namespace Database\Factories\User\OrderCar;

use App\Models\User\OrderCar\OrderCar;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderCarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderCar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payment_status' => 1,
            'sum' => 1000,
            'sum_discount' => 900,
            'order_number' => \Str::random(8)
        ];
    }
}

