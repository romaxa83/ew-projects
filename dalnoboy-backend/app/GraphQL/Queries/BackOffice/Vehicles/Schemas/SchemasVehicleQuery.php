<?php


namespace App\GraphQL\Queries\BackOffice\Vehicles\Schemas;


use App\GraphQL\Queries\Common\Vehicles\Schemas\BaseSchemasVehicleQuery;

class SchemasVehicleQuery extends BaseSchemasVehicleQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
