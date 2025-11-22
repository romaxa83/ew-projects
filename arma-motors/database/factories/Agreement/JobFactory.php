<?php

namespace Database\Factories\Agreement;

use App\Models\Agreement\Agreement;
use App\Models\Agreement\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'sum' => (string)$this->faker->randomFloat(),
            'agreement_id' => Agreement::factory(),
        ];
    }
}
