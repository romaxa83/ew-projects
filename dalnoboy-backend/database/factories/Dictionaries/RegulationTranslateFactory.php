<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\RegulationTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method RegulationTranslate|Collection create(array $attributes = [])
 */
class RegulationTranslateFactory extends Factory
{
    protected $model = RegulationTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
