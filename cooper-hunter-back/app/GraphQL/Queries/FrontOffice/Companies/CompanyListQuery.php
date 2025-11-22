<?php

namespace App\GraphQL\Queries\FrontOffice\Companies;

use App\GraphQL\Queries\Common\Companies\BaseCompanyListQuery;

class CompanyListQuery extends BaseCompanyListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setDealerGuard();
    }

    protected function initArgs(array $args): array
    {
        $args['corporation_id'] = $this->user()->company->corporation_id;

        return $args;
    }
}
