<?php

namespace Database\Factories\Catalog\Categories;

use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method CategoryTranslation|CategoryTranslation[]|Collection create(array $attrs = [])
 */
class CategoryTranslationFactory extends BaseTranslationFactory
{
    protected $model = CategoryTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->unique->word;

        return [
            'title' => $title,
            'description' => $this->faker->sentence,
            'language' => Language::factory(),
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}

