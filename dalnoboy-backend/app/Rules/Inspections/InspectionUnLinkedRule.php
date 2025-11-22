<?php

namespace App\Rules\Inspections;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Inspections\Inspection;
use Illuminate\Contracts\Validation\Rule;

class InspectionUnLinkedRule implements Rule
{
    private const ERROR_INCORRECT_FORM = 'inspections.linked.incorrect_vehicle_form';
    private const ERROR_MAIN_HAS_NOT_TRAILER = 'inspections.linked.main_has_not_trailer';

    private string $error = self::ERROR_INCORRECT_FORM;

    public function passes($attribute, mixed $value): bool
    {
        $inspection = Inspection::find($value);

        $mainInspection = ($inspection->vehicle->form->isNot(VehicleFormEnum::MAIN) && $inspection->main)
            ? $inspection->main
            : $inspection;

        $this->error = self::ERROR_MAIN_HAS_NOT_TRAILER;

        return $mainInspection->trailer()
            ->exists();
    }

    public function message(): string
    {
        return trans($this->error);
    }
}
