<?php

namespace App\Rules\WarrantyRegistrations;

use Illuminate\Contracts\Validation\Rule;

class WithoutDuplicateRule implements Rule
{
    public function __construct(protected $args)
    {}

    public function passes($attribute, $value): bool
    {
        $sns = data_get($this->args, 'serial_numbers', []);
        if(!empty($sns)){
            return count(array_unique($sns)) === count($sns);
        }

        return false;
    }

    public function message(): string
    {
        return __('validation.custom.duplicate_serial_numbers');
    }
}
