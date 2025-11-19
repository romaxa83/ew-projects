<?php

namespace Wezom\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;
use Wezom\Core\ValueObjects\Phone;

class PhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            Phone::from($value);
        } catch (Throwable) {
            $fail(__('core::exceptions.casts.phone'));
        }
    }
}
