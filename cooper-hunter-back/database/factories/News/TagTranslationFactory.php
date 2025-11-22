<?php

namespace Database\Factories\News;

use App\Models\Localization\Language;
use App\Models\News\Tag;
use App\Models\News\TagTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|TagTranslation[]|TagTranslation create(array $attributes = [])
 */
class TagTranslationFactory extends BaseTranslationFactory
{
    protected $model = TagTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Tag::factory(),
            'title' => $this->faker->sentence,
            'language' => Language::factory(),
        ];
    }
}
