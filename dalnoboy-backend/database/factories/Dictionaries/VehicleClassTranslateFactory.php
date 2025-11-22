<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\VehicleClassTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method VehicleClassTranslate|Collection create(array $attributes = [])
 */
class VehicleClassTranslateFactory extends Factory
{
    protected $model = VehicleClassTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
