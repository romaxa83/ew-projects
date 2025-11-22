<?php

namespace Database\Factories\Catalog\Features;

use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Features\SpecificationTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|SpecificationTranslation[]|SpecificationTranslation create(array $attributes = [])
 */
class SpecificationTranslationFactory extends BaseTranslationFactory
{
    protected $model = SpecificationTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Specification::factory(),
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
