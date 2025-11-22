<?php
namespace Database\Factories\Vehicles;

use App\Models\Vehicles\Trailer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrailerFactory extends Factory
{
    protected $model = Trailer::class;

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
            'license_plate' => $this->faker->bothify('###-???'),
            'temporary_plate' => $this->faker->bothify('###-???'),
            'notes' => $this->faker->text,
        ];
    }
}



