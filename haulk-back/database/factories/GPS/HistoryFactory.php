<?php
namespace Database\Factories\GPS;

use App\Models\GPS\History;
use App\Models\Vehicles\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryFactory extends Factory
{
    protected $model = History::class;

    public function definition(): array
    {
        return [
            'device_id' => null,
            'received_at' => now()->timestamp,
            'truck_id' => (factory(Truck::class)->create())->id,
            'trailer_id' => null,
            'latitude' => 49.069782,
            'longitude' => 28.632826,
            'heading' => 273.61,
            'vehicle_mileage' => 200,
            'speed' => 50,
            'device_battery_level' => 50,
            'device_battery_charging_status' => true,
            'event_type' => History::EVENT_DRIVING,
            'event_duration' => null,
            'company_id' => null,
            'driver_id' => null,
            'old_driver_id' => null,
            'is_speeding' => false,
            'last_received_at' => null,
        ];
    }
}

