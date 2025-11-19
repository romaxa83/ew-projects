<?php

namespace Wezom\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[a-zA-Z\x{0400}-\x{04FF} \-\']{2,250}$/u', $value)) {
            $fail(
                __(
                    'core::validation.custom.name.name-rule',
                    ['attribute' => __('core::validation.attributes.' . $attribute)]
                )
            );
        }
    }
}
