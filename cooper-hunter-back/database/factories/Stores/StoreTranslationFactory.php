<?php

namespace Database\Factories\Stores;

use App\Models\Stores\Store;
use App\Models\Stores\StoreTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|StoreTranslation[]|StoreTranslation create(array $attributes = [])
 */
class StoreTranslationFactory extends BaseTranslationFactory
{
    protected $model = StoreTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Store::factory(),
            'title' => $this->faker->word,
        ];
    }
}
