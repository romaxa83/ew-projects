<?php


namespace App\GraphQL\Queries\FrontOffice\Vehicles;


use App\GraphQL\Queries\Common\Vehicles\BaseVehiclesQuery;

class VehiclesQuery extends BaseVehiclesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
