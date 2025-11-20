<?php

namespace App\Rules;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class PasswordRule implements Rule
{
    protected int $minLength;

    public function __construct(?int $minLength = null)
    {
        $this->minLength = $minLength ?? User::MIN_LENGTH_PASSWORD;
    }

    public function passes($attribute, $value): bool
    {
        return preg_match('/^(?=.*[A-Za-z\d])(?=.*\d)(?=.*[A-Z].*)[A-Za-z\d]{' . $this->minLength . ',250}$/', $value);
    }

    public function message(): string
    {
        return __('validation.custom.password.password-rule', ['min' => $this->minLength]);
    }
}
