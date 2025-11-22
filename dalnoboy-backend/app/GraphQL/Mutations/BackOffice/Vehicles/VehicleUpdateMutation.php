<?php


namespace App\GraphQL\Mutations\BackOffice\Vehicles;


use App\GraphQL\Mutations\Common\Vehicles\BaseVehicleUpdateMutation;

class VehicleUpdateMutation extends BaseVehicleUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
