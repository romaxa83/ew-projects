<?php

namespace App\Rules;

use Auth;
use Hash;
use Illuminate\Contracts\Validation\Rule;

class BooleanRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return is_bool(to_bool($value));
    }

    public function message(): string
    {
        return trans('validation.boolean');
    }
}

