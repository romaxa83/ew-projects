<?php

namespace App\Validators\Orders;

use App\Models\Orders\Inspection;

class ExteriorDeliveryInspectionPhotoValidator extends ExteriorInspectionPhotoValidator
{
    protected function getInspection(): Inspection
    {
        return $this->vehicle->deliveryInspection;
    }
}
