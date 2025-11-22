<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Vehicle;
use App\Validators\Orders\ExteriorDeliveryInspectionPhotoValidator;
use App\Validators\Orders\ExteriorPickupInspectionPhotoValidator;

class DeliveryInspectExteriorRequest extends AbstractInspectExteriorRequest
{
    protected function getExteriorInspectionPhotoValidator(Vehicle $vehicle): ExteriorDeliveryInspectionPhotoValidator
    {
        return new ExteriorDeliveryInspectionPhotoValidator($vehicle);
    }
}
