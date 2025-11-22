<?php


namespace App\GraphQL\Queries\FrontOffice\Branches;


use App\GraphQL\Queries\Common\Branches\BaseBranchesListQuery;

class BranchesListQuery extends BaseBranchesListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
