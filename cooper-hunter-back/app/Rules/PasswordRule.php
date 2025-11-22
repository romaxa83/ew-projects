<?php

namespace App\Rules;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class PasswordRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return mb_strlen($value) >= User::MIN_LENGTH_PASSWORD;
    }

    public function message(): string
    {
        return __('validation.custom.password.password-rule');
    }
}
