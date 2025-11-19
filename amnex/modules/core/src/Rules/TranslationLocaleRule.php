<?php

declare(strict_types=1);

namespace Wezom\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Wezom\Core\Models\Language;

class TranslationLocaleRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Language::query()->where('slug', $value)->exists()) {
            $fail(__('core::validation.custom.site_not_valid_locale'));
        }
    }
}
