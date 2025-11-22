<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\OptionAnswerTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|OptionAnswerTranslation[]|OptionAnswerTranslation create(array $attributes = [])
 */
class OptionAnswerTranslationFactory extends BaseTranslationFactory
{
    protected $model = OptionAnswerTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => OptionAnswer::factory(),
            'text' => $this->faker->text,
            'language' => Language::factory(),
        ];
    }
}

