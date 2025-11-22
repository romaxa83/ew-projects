<?php

namespace App\Foundations\Modules\Localization\Factories;

use App\Foundations\Modules\Localization\Enums\Translations\TranslationPlace;
use App\Foundations\Modules\Localization\Models\Translation;
use Database\Factories\BaseFactory;

class TranslationFactory extends BaseFactory
{
    protected $model = Translation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place' => TranslationPlace::SITE,
            'key' => fake()->name(),
            'text' => fake()->name(),
            'lang' => 'en',
        ];
    }
}
