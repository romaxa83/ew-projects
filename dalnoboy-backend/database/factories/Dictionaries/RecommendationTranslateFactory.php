<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\RecommendationTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method RecommendationTranslate|Collection create(array $attributes = [])
 */
class RecommendationTranslateFactory extends Factory
{
    protected $model = RecommendationTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
