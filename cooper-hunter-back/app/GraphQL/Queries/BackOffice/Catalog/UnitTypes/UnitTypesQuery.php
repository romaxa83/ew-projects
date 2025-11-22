<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\UnitTypes;

use App\GraphQL\Queries\Common\Catalog\UnitType\BaseUnitTypesQuery;

class UnitTypesQuery extends BaseUnitTypesQuery
{
    public function __construct()
    {
        $this->setAdminGuard();
    }
}
