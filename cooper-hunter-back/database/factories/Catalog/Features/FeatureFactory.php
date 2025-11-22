<?php

namespace Database\Factories\Catalog\Features;

use App\Models\Catalog\Features\Feature;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Feature|Feature[]|Collection create(array $attrs = [])
 */
class FeatureFactory extends BaseFactory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'sort' => 0,
            'guid' => $this->faker->uuid
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }

    public function web(): static
    {
        return $this->state(
            [
                'display_in_web' => true
            ]
        );
    }
}

