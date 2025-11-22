<?php
namespace Database\Factories\GPS;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GPS\Message;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'imei' => $this->faker->bothify('#####????###'),
            'received_at' => now()->timestamp,
            'latitude' => 49.069782,
            'longitude' => 28.632826,
            'heading' => 273.61,
            'vehicle_mileage' => 200,
            'speed' => 50,
            'device_battery_level' => 10,
            'device_battery_charging_status' => false,
            'driving' => true,
            'idling' => false,
            'engine_off' => false,
        ];
    }
}

