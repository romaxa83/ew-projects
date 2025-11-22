<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TranslationsArrayValidator implements Rule
{
    public function passes($attribute, $value): bool
    {
        $givenSlugs = collect($value)->pluck('language')->shuffle()->toArray();
        $neededSlugs = languages()->pluck('slug')->shuffle()->toArray();

        $defaultLanguage = defaultLanguage()->slug;

        return in_array($defaultLanguage, $givenSlugs) && empty(array_diff($givenSlugs, $neededSlugs));
    }

    public function message(): string
    {
        return __('validation.translates_array_validation_failed');
    }
}
