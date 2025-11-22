<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleClassesQuery;

class VehicleClassesQuery extends BaseVehicleClassesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
