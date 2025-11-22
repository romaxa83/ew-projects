<?php

namespace App\GraphQL\Queries\FrontOffice\Companies;

use App\GraphQL\Queries\Common\Companies\BaseCompaniesQuery;
use Core\Exceptions\TranslatedException;

class CompaniesQuery extends BaseCompaniesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setDealerGuard();
    }

    protected function initArgs(array $args): array
    {
        $args['corporation_id'] = $this->user()->company->corporation_id;
//        if(!$this->user()->isMain()){
//            throw new TranslatedException(__("exceptions.dealer.not_main"), 502);
//        }

        return $args;
    }
}
