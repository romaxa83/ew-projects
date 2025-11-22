<?php

namespace Database\Factories\Faq;

use App\Models\Faq\Faq;
use App\Models\Faq\FaqTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|FaqTranslation[]|FaqTranslation create(array $attributes = [])
 */
class FaqTranslationFactory extends BaseTranslationFactory
{
    protected $model = FaqTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Faq::factory(),
            'question' => $this->faker->sentence,
            'answer' => $this->faker->text,
            'language' => Language::factory(),
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
