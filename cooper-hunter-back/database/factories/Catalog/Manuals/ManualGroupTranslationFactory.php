<?php

namespace Database\Factories\Catalog\Manuals;

use App\Models\Catalog\Manuals\ManualGroupTranslation;
use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ManualGroupTranslation[]|ManualGroupTranslation create(array $attributes = [])
 */
class ManualGroupTranslationFactory extends BaseTranslationFactory
{
    protected $model = ManualGroupTranslation::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'language' => Language::factory()
        ];
    }
}
