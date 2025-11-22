<?php

namespace Database\Factories\Agreement;

use App\Models\Agreement\Agreement;
use App\Models\Agreement\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartFactory extends Factory
{
    protected $model = Part::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'sum' => (string)$this->faker->randomFloat(),
            'qty' => (string)$this->faker->randomFloat(),
            'agreement_id' => Agreement::factory(),
        ];
    }
}
