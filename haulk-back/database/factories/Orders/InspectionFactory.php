<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{

    protected $model = Inspection::class;

    public function definition(): array
    {
        return [
            'has_vin_inspection' => true,
            'odometer' => $this->faker->numberBetween(10000, 9999999),
            'notes' => $this->faker->text,
        ];
    }
}
