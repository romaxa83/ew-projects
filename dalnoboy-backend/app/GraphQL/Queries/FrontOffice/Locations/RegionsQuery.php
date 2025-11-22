<?php


namespace App\GraphQL\Queries\FrontOffice\Locations;


use App\GraphQL\Queries\Common\Locations\BaseRegionsQuery;

class RegionsQuery extends BaseRegionsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
