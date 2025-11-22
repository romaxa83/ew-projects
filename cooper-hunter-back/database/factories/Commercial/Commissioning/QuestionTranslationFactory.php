<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Question;
use App\Models\Commercial\Commissioning\QuestionTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|QuestionTranslation[]|QuestionTranslation create(array $attributes = [])
 */
class QuestionTranslationFactory extends BaseTranslationFactory
{
    protected $model = QuestionTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Question::factory(),
            'text' => $this->faker->text,
            'language' => Language::factory(),
        ];
    }
}
