<?php

namespace App\GraphQL\Queries\BackOffice\Companies;

use App\GraphQL\Queries\Common\Companies\BaseCompaniesQuery;

class CompaniesQuery extends BaseCompaniesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
