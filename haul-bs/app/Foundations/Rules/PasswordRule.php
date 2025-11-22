<?php

namespace App\Foundations\Rules;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class PasswordRule implements Rule
{

    public function passes($attribute, $value): bool
    {
        return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{' . User::MIN_LENGTH_PASSWORD . ',250}$/', $value);
    }

    public function message(): string
    {
        return __('validation.custom.password.password_rule');
    }
}
