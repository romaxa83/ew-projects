<?php

namespace App\Validators\Orders;

use App\Models\Orders\Inspection;

class ExteriorPickupInspectionPhotoValidator extends ExteriorInspectionPhotoValidator
{
    protected function getInspection(): Inspection
    {
        return $this->vehicle->pickupInspection;
    }
}
