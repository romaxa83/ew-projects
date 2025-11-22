<?php

namespace Database\Factories\Catalog\Features;

use App\Models\Catalog\Features\FeatureTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @method FeatureTranslation|FeatureTranslation[]|Collection create(array $attrs = [])
 */
class FeatureTranslationFactory extends BaseTranslationFactory
{
    protected $model = FeatureTranslation::class;

    public function definition(): array
    {
        return [
            'title' => $title = $this->faker->word,
            'slug' => Str::slug($title),
            'description' => $this->faker->word,
        ];
    }
}
