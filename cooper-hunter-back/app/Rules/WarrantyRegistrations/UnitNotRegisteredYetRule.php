<?php

namespace App\Rules\WarrantyRegistrations;

use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Illuminate\Contracts\Validation\Rule;

class UnitNotRegisteredYetRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return WarrantyRegistrationUnitPivot::query()
//            ->notDeleted()
            ->where('serial_number', $value)
            ->doesntExist();
    }

    public function message(): string
    {
        return __('validation.custom.unit-serial-number-used');
    }
}
