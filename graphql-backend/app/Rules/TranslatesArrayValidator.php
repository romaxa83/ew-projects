<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TranslatesArrayValidator implements Rule
{
    public function passes($attribute, $value): bool
    {
        $givenSlugs = collect($value)->pluck('language')->shuffle()->toArray();
        $neededSlugs = languages()->pluck('slug')->shuffle()->toArray();

        return array_diff($neededSlugs, $givenSlugs) === array_diff($givenSlugs, $neededSlugs);
    }

    public function message(): string
    {
        return __('validation.translates_array_validation_failed');
    }
}
