<?php

namespace Database\Factories\News;

use App\Models\Localization\Language;
use App\Models\News\News;
use App\Models\News\NewsTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|NewsTranslation[]|NewsTranslation create(array $attributes = [])
 */
class NewsTranslationFactory extends BaseTranslationFactory
{
    protected $model = NewsTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => News::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'short_description' => $this->faker->text,
            'language' => Language::factory(),
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
