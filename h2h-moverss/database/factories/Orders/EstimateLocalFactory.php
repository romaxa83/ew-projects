<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class EstimateLocalFactory extends BaseFactory
{
    protected $model = Order\Estimate\Local::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'hours_min' => 2.0,
            'hours_max' => 3.60,
            'rate' => 170.00,
            'rate_auto' => 200,
            'is_auto' => 1,
            'mileage' => null,
        ];
    }
}
