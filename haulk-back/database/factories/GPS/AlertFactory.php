<?php
namespace Database\Factories\GPS;

use App\Models\GPS\Alert;
use App\Models\Vehicles\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'truck_id' => (factory(Truck::class)->create())->id,
            'received_at' => now(),
            'latitude' => 49.069782,
            'longitude' => 28.632826,
            'speed' => 100,
            'alert_type' => Alert::ALERT_TYPE_SPEEDING,
            'alert_subtype' => null,
        ];
    }
}

