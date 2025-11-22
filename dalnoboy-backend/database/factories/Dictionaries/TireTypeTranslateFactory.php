<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireTypeTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireTypeTranslate|Collection create(array $attributes = [])
 */
class TireTypeTranslateFactory extends Factory
{
    protected $model = TireTypeTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
