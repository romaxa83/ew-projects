<?php

namespace App\Foundations\Modules\Seo\Factories;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use Database\Factories\BaseFactory;

class SeoFactory extends BaseFactory
{
    protected $model = Seo::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
//        $model = Brand::factory()->create();

        return [
            'model_id' => null,
            'model_type' => null,
            'h1' => fake()->sentence,
            'title' => fake()->sentence,
            'keywords' => fake()->sentence,
            'desc' => fake()->sentence,
            'text' => fake()->sentence,
        ];
    }
}
