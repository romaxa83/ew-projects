<?php

namespace Database\Factories\Warranty\WarrantyInfo;

use App\Models\Localization\Language;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|WarrantyInfoTranslation[]|WarrantyInfoTranslation create(array $attributes = [])
 */
class WarrantyInfoTranslationFactory extends BaseTranslationFactory
{
    protected $model = WarrantyInfoTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => WarrantyInfo::factory(),
            'description' => $this->faker->sentence,
            'notice' => $this->faker->text,
            'language' => Language::factory(),
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
