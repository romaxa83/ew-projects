<?php

namespace Database\Factories\Saas\Company;

use App\Models\Saas\Company\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{

    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->email,
            'usdot' => $this->faker->numberBetween(1000, 10000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
