<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\VehicleTypeTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method VehicleTypeTranslate|Collection create(array $attributes = [])
 */
class VehicleTypeTranslateFactory extends Factory
{
    protected $model = VehicleTypeTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
