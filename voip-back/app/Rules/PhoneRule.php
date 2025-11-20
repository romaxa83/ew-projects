<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return preg_match('/^\+?380\d{7,17}$/', $value);
    }

    public function message(): string
    {
        return __('validation.custom.phone.valid');
    }
}
