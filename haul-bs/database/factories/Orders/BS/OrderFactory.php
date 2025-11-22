<?php

namespace Database\Factories\Orders\BS;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Orders\BS\Order;
use App\Models\Vehicles\Truck;
use Database\Factories\BaseFactory;
use Database\Factories\Users\UserFactory;
use Database\Factories\Vehicles\TruckFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\BS\Order>
 */
class OrderFactory extends BaseFactory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_number' => date('Ymd-' . fake()->numberBetween(1, 100)),
            'vehicle_type' => Truck::MORPH_NAME,
            'vehicle_id' => TruckFactory::new(),
            'discount' => fake()->randomFloat(2, 0, 99),
            'tax_labor' => fake()->randomFloat(2, 0, 99),
            'tax_inventory' => fake()->randomFloat(2, 0, 99),
            'implementation_date' => now()->format('Y-m-d H:i'),
            'mechanic_id' => UserFactory::new(),
            'status' => OrderStatus::New->value,
            'due_date' => now()->format('Y-m-d H:i'),
            'profit' => null,
            'parts_cost' => null,
        ];
    }
}

