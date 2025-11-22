<?php

namespace App\Foundations\Rules;

use Illuminate\Contracts\Validation\Rule;

class RequiredWithout implements Rule
{
    protected array $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function passes($attribute, $value): bool
    {
//        dd($this->fields, $value, $attribute, request()->has('delivery_address.id'));

        foreach ($this->fields as $field) {
            if (!request()->has($field)) {
                return !empty($value);
            }
        }
        return true;
    }

    public function message(): string
    {
        return 'The :attribute field is required without :values.';
    }
}
