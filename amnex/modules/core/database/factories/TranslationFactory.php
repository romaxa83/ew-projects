<?php

declare(strict_types=1);

namespace Wezom\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\Models\Translation;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        $word = $this->faker->unique()->word();

        return [
            'side' => TranslationSideEnum::COMMON,
            'key' => 'core::validation.message.' . $word,
            'language' => config('translations.admin.default'),
            'text' => $word,
        ];
    }
}
