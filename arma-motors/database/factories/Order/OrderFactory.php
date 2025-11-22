<?php

namespace Database\Factories\Order;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'uuid' => 'ww',
        ];
    }
}
