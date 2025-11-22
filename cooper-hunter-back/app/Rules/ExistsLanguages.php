<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ExistsLanguages implements Rule
{

    public function passes($attribute, $value): bool
    {
        return (bool)app('localization')
            ->getAllLanguages()
            ->where('slug', $value)
            ->first();
    }

    public function message(): string
    {
        return __('validation.custom.lang.exist-languages');
    }
}
