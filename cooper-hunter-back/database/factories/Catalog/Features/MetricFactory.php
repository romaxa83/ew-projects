<?php

namespace Database\Factories\Catalog\Features;

use App\Models\Catalog\Features\Metric;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Metric|Metric[]|Collection create(array $attrs = [])
 */
class MetricFactory extends BaseFactory
{
    protected $model = Metric::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->lexify
        ];
    }
}


