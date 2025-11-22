<?php

namespace Database\Factories\Catalog\Features;

use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Value|Value[]|Collection create(array $attrs = [])
 */
class ValueFactory extends BaseFactory
{
    protected $model = Value::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'title' => $this->faker->unique->word,
            'sort' => 0,
            'feature_id' => Feature::factory(),
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }
}


