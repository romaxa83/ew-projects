<?php

namespace Database\Factories\Stores;

use App\Models\Stores\StoreCategory;
use App\Models\Stores\StoreCategoryTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|StoreCategoryTranslation[]|StoreCategoryTranslation create(array $attributes = [])
 */
class StoreCategoryTranslationFactory extends BaseTranslationFactory
{
    protected $model = StoreCategoryTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => StoreCategory::factory(),
            'title' => $this->faker->word,
        ];
    }
}
