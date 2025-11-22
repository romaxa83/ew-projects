<?php


namespace App\GraphQL\Queries\BackOffice\Inspections;


use App\GraphQL\Queries\Common\Inspections\BaseInspectionsQuery;

class InspectionsQuery extends BaseInspectionsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
