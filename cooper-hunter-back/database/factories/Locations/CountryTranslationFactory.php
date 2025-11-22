<?php

namespace Database\Factories\Locations;

use App\Models\Locations\CountryTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|CountryTranslation[]|CountryTranslation create(array $attributes = [])
 */
class CountryTranslationFactory extends Factory
{
    protected $model = CountryTranslation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
        ];
    }
}
