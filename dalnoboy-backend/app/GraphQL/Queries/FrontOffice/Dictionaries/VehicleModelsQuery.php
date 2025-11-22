<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleModelsQuery;

class VehicleModelsQuery extends BaseVehicleModelsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
