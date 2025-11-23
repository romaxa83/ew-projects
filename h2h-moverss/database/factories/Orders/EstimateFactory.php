<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class EstimateFactory extends BaseFactory
{
    protected $model = Order\Estimate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => 'local',
            'is_locked' => 0,
            'trucks' => 1,
            'crews' => 2,
            'discount_type' => 'sum',
            'discount_value' => 66.00,
            'fee_type' => 'percent',
            'travel_fee' => null,
            'calculated_moving_min_value' => null,
            'calculated_moving_max_value' => null,
            'calculated_extra_services' => null,
            'calculated_extra_materials' => null,
            'calculated_moving_time' => null,
            'calculated_moving_distance' => null,
            'calculated_moving_distance_auto' => null,
            'calculated_moving_distance_is_auto' => 0,
            'dispatch_allowed' => 0,
        ];
    }
}
