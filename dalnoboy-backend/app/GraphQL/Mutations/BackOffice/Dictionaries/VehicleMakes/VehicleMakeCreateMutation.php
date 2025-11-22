<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\GraphQL\Mutations\Common\Dictionaries\VehicleMakes\BaseVehicleMakeCreateMutation;

class VehicleMakeCreateMutation extends BaseVehicleMakeCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();
    }
}
