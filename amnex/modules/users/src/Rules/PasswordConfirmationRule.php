<?php

declare(strict_types=1);

namespace Wezom\Users\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class PasswordConfirmationRule implements ValidationRule
{
    public function __construct(private mixed $compareWith)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === $this->compareWith) {
            return;
        }

        $fail(__('users::validation.site.custom.password.confirmation'));
    }
}
