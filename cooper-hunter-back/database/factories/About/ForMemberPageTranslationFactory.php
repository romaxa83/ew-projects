<?php

namespace Database\Factories\About;

use App\Models\About\ForMemberPage;
use App\Models\About\ForMemberPageTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ForMemberPageTranslation[]|ForMemberPageTranslation create(array $attributes = [])
 */
class ForMemberPageTranslationFactory extends BaseTranslationFactory
{
    protected $model = ForMemberPageTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => ForMemberPage::factory(),
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
