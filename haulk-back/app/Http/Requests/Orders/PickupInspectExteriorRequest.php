<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Vehicle;
use App\Validators\Orders\ExteriorPickupInspectionPhotoValidator;

class PickupInspectExteriorRequest extends AbstractInspectExteriorRequest
{
    protected function getExteriorInspectionPhotoValidator(Vehicle $vehicle): ExteriorPickupInspectionPhotoValidator
    {
        return new ExteriorPickupInspectionPhotoValidator($vehicle);
    }
}
