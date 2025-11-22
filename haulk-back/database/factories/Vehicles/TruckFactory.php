<?php
namespace Database\Factories\Vehicles;

use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class TruckFactory extends Factory
{
    protected $model = Truck::class;

    public function definition(): array
    {
        return [
            'carrier_id' => 1,
            'broker_id' => null,
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => $this->faker->bothify('##??'),
            'make' => 'Audi',
            'model' => 'A3',
            'year' => $this->faker->numberBetween(2000, 2023),
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => $this->faker->bothify('###-???'),
            'temporary_plate' => $this->faker->bothify('###-???'),
            'notes' => $this->faker->text,
            'gps_device_id' => null,
        ];
    }
}


