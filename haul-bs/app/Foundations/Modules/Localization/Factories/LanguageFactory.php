<?php

namespace App\Foundations\Modules\Localization\Factories;

use App\Foundations\Modules\Localization\Models\Language;
use Database\Factories\BaseFactory;

class LanguageFactory extends BaseFactory
{
    protected $model = Language::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->languageCode,
            'slug' => fake()->languageCode,
            'native' => fake()->languageCode,
            'default' => false,
            'active' => true,
            'sort' => 1,
        ];
    }
}
