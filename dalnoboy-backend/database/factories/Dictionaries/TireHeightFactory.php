<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireHeight;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireHeight|TireHeight[]|Collection create(array $attributes = [])
 */
class TireHeightFactory extends Factory
{
    protected $model = TireHeight::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'value' => $this->faker->numerify,
        ];
    }
}
