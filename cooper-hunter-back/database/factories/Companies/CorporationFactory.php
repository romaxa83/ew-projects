<?php

namespace Database\Factories\Companies;

use App\Models\Companies\Corporation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Corporation|Corporation[]|Collection create(array $attributes = [])
 */
class CorporationFactory extends Factory
{
    protected $model = Corporation::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'name' => $this->faker->company,
        ];
    }
}
