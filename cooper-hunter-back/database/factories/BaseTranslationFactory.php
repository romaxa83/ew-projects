<?php

namespace Database\Factories;

use App\Models\Localization\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

abstract class BaseTranslationFactory extends Factory
{
    public function allLocales(): static
    {
        return $this
            ->count(2)
            ->sequence(
                ['language' => 'en'],
                ['language' => 'es'],
            );
    }

    public function esLocale(): static
    {
        return $this->locale('es');
    }

    public function locale(?string $locale = null): static
    {
        if (!$locale) {
            $locale = app()->getLocale();
        }

        $languageFactory = Language::query()
            ->where('slug', $locale)
            ->firstOr(
                static fn() => Language::factory()->locale($locale)->create()
            );

        return $this->state(
            [
                'language' => $languageFactory->slug
            ]
        );
    }

    public function enLocale(): static
    {
        return $this->locale('en');
    }
}
