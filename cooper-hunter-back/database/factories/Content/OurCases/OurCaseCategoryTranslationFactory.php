<?php

namespace Database\Factories\Content\OurCases;

use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|OurCaseCategoryTranslation[]|OurCaseCategoryTranslation create(array $attributes = [])
 */
class OurCaseCategoryTranslationFactory extends BaseTranslationFactory
{
    protected $model = OurCaseCategoryTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => OurCaseCategory::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'language' => Language::factory(),
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
