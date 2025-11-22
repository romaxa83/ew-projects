<?php

namespace App\Rules\ExistsRules;

use App\Models\Catalog\Products\ProductSerialNumber;
use Illuminate\Contracts\Validation\Rule;

class SerialNumberExistsRule implements Rule
{
    protected string $serial;

    public function passes($attribute, $value): bool
    {
        $value = strtoupper($value);
        $this->serial = $value;

        return ProductSerialNumber::query()
            ->where('serial_number', $value)
            ->exists();
    }

    public function message(): string
    {
        return __('validation.custom.serial-number', ['serial' => $this->serial]);
    }
}
