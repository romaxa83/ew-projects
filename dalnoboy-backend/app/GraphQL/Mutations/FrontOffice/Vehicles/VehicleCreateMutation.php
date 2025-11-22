<?php


namespace App\GraphQL\Mutations\FrontOffice\Vehicles;


use App\GraphQL\Mutations\Common\Vehicles\BaseVehicleCreateMutation;

class VehicleCreateMutation extends BaseVehicleCreateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
