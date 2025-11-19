<?php

namespace Wezom\Core\Rules;

use Illuminate\Validation\Rule;
use Wezom\Core\Models\Language;

class LanguageRules
{
    public static function make(): array
    {
        return [
            'required',
            'distinct',
            'string',
            'max:3',
            Rule::exists(Language::class, 'slug'),
        ];
    }
}
