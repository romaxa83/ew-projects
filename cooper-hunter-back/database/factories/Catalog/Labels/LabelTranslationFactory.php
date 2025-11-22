<?php

namespace Database\Factories\Catalog\Labels;

use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Labels\LabelTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|LabelTranslation[]|LabelTranslation create(array $attributes = [])
 */
class LabelTranslationFactory extends BaseTranslationFactory
{
    protected $model = LabelTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Label::factory(),
            'title' => $this->faker->sentence,
            'language' => Language::factory()
        ];
    }
}
