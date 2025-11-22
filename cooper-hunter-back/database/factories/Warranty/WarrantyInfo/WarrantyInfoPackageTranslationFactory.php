<?php

namespace Database\Factories\Warranty\WarrantyInfo;

use App\Models\Localization\Language;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackageTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|WarrantyInfoPackageTranslation[]|WarrantyInfoPackageTranslation create(array $attributes = [])
 */
class WarrantyInfoPackageTranslationFactory extends BaseTranslationFactory
{
    protected $model = WarrantyInfoPackageTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => WarrantyInfoPackage::factory(),
            'title' => $this->faker->text,
            'description' => $this->faker->sentence,
            'language' => Language::factory()
        ];
    }
}
