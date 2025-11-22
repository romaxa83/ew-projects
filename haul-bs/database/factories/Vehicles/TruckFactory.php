<?php

namespace Database\Factories\Vehicles;

use App\Models\Vehicles\Truck;
use Database\Factories\BaseFactory;
use Database\Factories\Customers\CustomerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicles\Truck>
 */
class TruckFactory extends BaseFactory
{
    protected $model = Truck::class;

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
            'type' => 1,
            'gvwr' => $this->faker->numberBetween(2010, 2023),
        ];
    }
}
