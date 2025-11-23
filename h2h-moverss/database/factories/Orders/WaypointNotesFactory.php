<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use App\User;
use Database\Factories\BaseFactory;

class WaypointNotesFactory extends BaseFactory
{
    protected $model = Order\WaypointNotes::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'waypoint_id' => Order\Waypoint::factory(),
            'user_id' => User::factory(),
            'value' => $this->faker->sentence(),
            'deleted_at' => null,
        ];
    }
}
