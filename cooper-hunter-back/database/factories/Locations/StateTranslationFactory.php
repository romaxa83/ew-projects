<?php

namespace Database\Factories\Locations;

use App\Models\Locations\StateTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|StateTranslation[]|StateTranslation create(array $attributes = [])
 */
class StateTranslationFactory extends Factory
{
    protected $model = StateTranslation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
        ];
    }
}
