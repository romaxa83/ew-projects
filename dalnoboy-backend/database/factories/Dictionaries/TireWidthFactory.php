<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireWidth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireWidth|TireWidth[]|Collection create(array $attributes = [])
 */
class TireWidthFactory extends Factory
{
    protected $model = TireWidth::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'value' => $this->faker->numerify,
        ];
    }
}
