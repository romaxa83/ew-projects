<?php

namespace Database\Factories\Catalog\Solutions\Series;

use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Database\Factories\BaseFactory;

class SolutionSeriesFactory extends BaseFactory
{
    protected $model = SolutionSeries::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique->slug,
        ];
    }
}