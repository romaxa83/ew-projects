<?php

namespace Database\Factories\Orders;

use App\Models\BuildingType;
use App\Models\Order;
use App\Models\ParkingType;
use Database\Factories\BaseFactory;

class WaypointFactory extends BaseFactory
{
    protected $model = Order\Waypoint::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => 'destination',
            'state' => 'IL',
            'zip' => '61244',
            'city' => 'Chicago',
            'address' => '670 West Wayman Street',
            'ap' => '505',
            'parking_type_id' => ParkingType::factory(),
            'has_elevator' => 1,
            'building_type_id' => BuildingType::factory(),
            'flights_id' => 0,
            'sort' => 0,
            'lat' => 41.80746870,
            'lng' => -87.70740880,
            'miscs' => null,
            'deleted_at' => null,
        ];
    }
}
