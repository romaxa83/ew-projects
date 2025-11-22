<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireModel|TireModel[]|Collection create(array $attributes = [])
 */
class TireModelFactory extends Factory
{
    protected $model = TireModel::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'is_moderated' => true,
            'tire_make_id' => TireMake::factory(),
            'title' => $this->faker->text,
        ];
    }
}
