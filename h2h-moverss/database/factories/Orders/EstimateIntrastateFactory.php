<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class EstimateIntrastateFactory extends BaseFactory
{
    protected $model = Order\Estimate\Intrastate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'rate' => 0.00,
            'rate_auto' => 3.60,
            'is_auto' => 0,
        ];
    }
}
