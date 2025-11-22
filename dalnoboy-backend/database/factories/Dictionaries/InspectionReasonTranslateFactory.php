<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\InspectionReasonTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method InspectionReasonTranslate|Collection create(array $attributes = [])
 */
class InspectionReasonTranslateFactory extends Factory
{
    protected $model = InspectionReasonTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
