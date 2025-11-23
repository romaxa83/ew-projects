<?php

namespace Database\Factories\Orders;

use App\Enums\Orders\EstimateType;
use App\Models\Order;
use Database\Factories\BaseFactory;

class EstimateCalculatedFactory extends BaseFactory
{
    protected $model = Order\Estimate\Calculated::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'estimate_type' => EstimateType::Intrastate(),
            'title' => 'materials',
            'value' => '$534',
        ];
    }
}
