<?php

namespace Database\Factories\Vehicles;

use App\Models\Vehicles\Trailer;
use Database\Factories\BaseFactory;
use Database\Factories\Customers\CustomerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicles\Trailer>
 */
class TrailerFactory extends BaseFactory
{
    protected $model = Trailer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'customer_id' => CustomerFactory::new(),
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => $this->faker->bothify('##??'),
            'make' => 'Audi',
            'model' => 'A3',
            'year' => $this->faker->numberBetween(2010, 2023),
            'notes' => $this->faker->text,
            'license_plate' => $this->faker->bothify('###-???'),
            'color' => $this->faker->colorName,
            'gvwr' => $this->faker->numberBetween(2010, 2023),
            'type' => 1,
        ];
    }
}
