<?php

namespace Database\Factories\Content\OurCases;

use App\Models\Content\OurCases\OurCaseCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|OurCaseCategory[]|OurCaseCategory create(array $attributes = [])
 */
class OurCaseCategoryFactory extends Factory
{
    protected $model = OurCaseCategory::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'slug' => $this->faker->slug,
        ];
    }
}
