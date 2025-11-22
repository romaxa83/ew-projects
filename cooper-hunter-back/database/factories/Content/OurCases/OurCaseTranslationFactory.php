<?php

namespace Database\Factories\Content\OurCases;

use App\Models\Content\OurCases\OurCase;
use App\Models\Content\OurCases\OurCaseTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|OurCaseTranslation[]|OurCaseTranslation create(array $attributes = [])
 */
class OurCaseTranslationFactory extends BaseTranslationFactory
{
    protected $model = OurCaseTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => OurCase::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'language' => Language::factory(),
        ];
    }
}
