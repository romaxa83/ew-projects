<?php

namespace App\Rules\Import;

use Illuminate\Contracts\Validation\Rule;

class ExtensionRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return in_array(request()->file('file')->getClientOriginalExtension(), ['xls', 'xlsx']);
    }

    public function message(): string
    {
        return 'Invalid file extension';
    }
}
