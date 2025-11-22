<?php

namespace App\Rules\Catalog;

use App\Models\Catalog\Products\ProductSerialNumber;
use Illuminate\Contracts\Validation\Rule;

class UnitSerialNumberRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return ProductSerialNumber::query()
            ->where('product_id', $value['product_id'])
            ->where('serial_number', $value['serial_number'])
            ->exists();
    }

    public function message(): string
    {
        return __('validation.custom.unit-serial-number');
    }
}
