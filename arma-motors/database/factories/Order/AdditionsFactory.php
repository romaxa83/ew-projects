<?php

namespace Database\Factories\Order;

use App\Models\Order\Additions;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdditionsFactory extends Factory
{
    protected $model = Additions::class;

    public function definition()
    {
        return [
            'order_id' => 1,
        ];
    }
}
