<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class EstimateInterstateFactory extends BaseFactory
{
    protected $model = Order\Estimate\Interstate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'estimate_rate' => 'expedited',
            'rate' => 0.00,
            'rate_auto' => 3.60,
            'is_auto' => 0,
            'packing' => 8250.00,
            'unpacking' => 0.00,
            'shuttle_pickup' => 0,
            'shuttle_delivery' => 0,
            'delivery_days' => '5 Day',
        ];
    }
}
