<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\ProblemTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method ProblemTranslate|Collection create(array $attributes = [])
 */
class ProblemTranslateFactory extends Factory
{
    protected $model = ProblemTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
