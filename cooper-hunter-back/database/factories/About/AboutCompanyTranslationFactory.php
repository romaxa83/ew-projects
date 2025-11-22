<?php

namespace Database\Factories\About;

use App\Models\About\AboutCompanyTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|AboutCompanyTranslation[]|AboutCompanyTranslation create(array $attributes = [])
 */
class AboutCompanyTranslationFactory extends BaseTranslationFactory
{
    protected $model = AboutCompanyTranslation::class;

    public function definition(): array
    {
        return [
            'video_link' => $this->faker->imageUrl,
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'short_description' => $this->faker->sentence,
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
