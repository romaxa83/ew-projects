<?php

namespace Database\Factories\About;

use App\Models\About\AboutCompany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|AboutCompany[]|AboutCompany create(array $attributes = [])
 */
class AboutCompanyFactory extends Factory
{
    protected $model = AboutCompany::class;

    public function definition(): array
    {
        return [
            //
        ];
    }
}
