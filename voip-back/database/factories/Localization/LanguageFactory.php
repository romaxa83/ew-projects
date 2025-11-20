<?php

namespace Database\Factories\Localization;

use App\Models\Localization\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Language[]|Language create()
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $lang = $this->faker->languageCode;

        return [
            'name' => $lang,
            'slug' => $lang,
            'default' => false,
        ];
    }

    public function locale(string $locale): self
    {
        return $this->state(
            [
                'name' => $locale,
                'slug' => $locale,
            ]
        );
    }
}
