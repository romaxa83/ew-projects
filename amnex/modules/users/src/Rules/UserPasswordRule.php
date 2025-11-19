<?php

namespace Wezom\Users\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserPasswordRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minLength = config('users.min-password-length');
        $maxLength = config('users.max-password-length');

        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{' . $minLength . ',' . $maxLength . '}$/', $value)) {
            $fail(__('users::validation.site.custom.password.rule'));
        }
    }
}
