<?php

namespace App\Rules;

use App\ValueObjects\Phone;
use Illuminate\Contracts\Validation\Rule;
use Throwable;

class PhoneRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        try {
            new Phone($value);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function message(): string
    {
        return __('The value must be a valid phone number!', ['attribute' => 'phone']);
    }
}
