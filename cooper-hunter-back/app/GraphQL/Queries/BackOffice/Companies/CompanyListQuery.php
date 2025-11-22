<?php

namespace App\GraphQL\Queries\BackOffice\Companies;

use App\GraphQL\Queries\Common\Companies\BaseCompanyListQuery;

class CompanyListQuery extends BaseCompanyListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
