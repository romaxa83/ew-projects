<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireRelationshipTypeTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireRelationshipTypeTranslate|Collection create(array $attributes = [])
 */
class TireRelationshipTypeTranslateFactory extends Factory
{
    protected $model = TireRelationshipTypeTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
        ];
    }
}
