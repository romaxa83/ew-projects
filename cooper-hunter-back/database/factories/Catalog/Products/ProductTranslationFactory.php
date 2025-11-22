<?php

namespace Database\Factories\Catalog\Products;

use App\Models\Catalog\Products\ProductTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method ProductTranslation|ProductTranslation[]|Collection create(array $attrs = [])
 */
class ProductTranslationFactory extends BaseTranslationFactory
{
    protected $model = ProductTranslation::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->realText(),
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}

