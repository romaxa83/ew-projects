<?php

namespace Database\Factories\Companies;

use App\Models\Companies\Company;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Companies\Company>
 */
class CompanyFactory extends BaseFactory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }
}
