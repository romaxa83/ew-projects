<?php

namespace Database\Factories\Catalog\Features;

use App\Models\Catalog\Features\Specification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Specification[]|Specification create(array $attributes = [])
 */
class SpecificationFactory extends Factory
{
    protected $model = Specification::class;

    public function definition(): array
    {
        return [
            'icon' => $this->faker->word,
        ];
    }
}
