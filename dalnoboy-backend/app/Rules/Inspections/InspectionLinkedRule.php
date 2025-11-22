<?php

namespace App\Rules\Inspections;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Inspections\Inspection;
use Illuminate\Contracts\Validation\Rule;

class InspectionLinkedRule implements Rule
{
    private const ERROR_INCORRECT_FORM = 'inspections.linked.incorrect_vehicle_form';
    private const ERROR_MAIN_HAS_TRAILER = 'inspections.linked.main_has_trailer';
    private const ERROR_TRAILER_HAS_MAIN = 'inspections.linked.trailer_has_main';

    private string $error = self::ERROR_INCORRECT_FORM;

    public function __construct(private VehicleFormEnum $form)
    {
    }

    public function passes($attribute, mixed $value): bool
    {
        $inspection = Inspection::find($value);

        if ($inspection->vehicle->form->isNot($this->form->value)) {
            return false;
        }

        if ($this->form->is(VehicleFormEnum::MAIN)) {
            $this->error = self::ERROR_MAIN_HAS_TRAILER;
            return !$inspection->trailer()
                ->exists();
        } else {
            $this->error = self::ERROR_TRAILER_HAS_MAIN;
            return !$inspection->main()
                ->exists();
        }
    }

    public function message(): string
    {
        return trans($this->error);
    }
}
