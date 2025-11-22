<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireMake;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireMake|TireMake[]|Collection create(array $attributes = [])
 */
class TireMakeFactory extends Factory
{
    protected $model = TireMake::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'is_moderated' => true,
            'title' => $this->faker->text,
        ];
    }
}
