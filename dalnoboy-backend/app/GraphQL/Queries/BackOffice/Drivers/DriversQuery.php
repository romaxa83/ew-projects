<?php


namespace App\GraphQL\Queries\BackOffice\Drivers;


use App\GraphQL\Queries\Common\Drivers\BaseDriversQuery;

class DriversQuery extends BaseDriversQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
