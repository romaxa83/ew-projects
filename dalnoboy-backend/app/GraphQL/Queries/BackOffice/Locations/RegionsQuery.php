<?php


namespace App\GraphQL\Queries\BackOffice\Locations;


use App\GraphQL\Queries\Common\Locations\BaseRegionsQuery;

class RegionsQuery extends BaseRegionsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
