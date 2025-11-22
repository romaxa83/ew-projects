<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels;

use App\GraphQL\Mutations\Common\Dictionaries\VehicleModels\BaseVehicleModelCreateMutation;

class VehicleModelCreateMutation extends BaseVehicleModelCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();
    }
}
