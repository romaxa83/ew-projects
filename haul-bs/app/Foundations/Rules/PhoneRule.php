<?php

namespace App\Foundations\Rules;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class PhoneRule implements Rule
{

    public function passes($attribute, $value): bool
    {
        return preg_match('/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/', $value);
    }

    public function message(): string
    {
        return __('validation.custom.phone.phone_rule');
    }
}

