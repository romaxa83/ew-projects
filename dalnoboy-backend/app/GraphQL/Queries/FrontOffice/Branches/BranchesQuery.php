<?php


namespace App\GraphQL\Queries\FrontOffice\Branches;


use App\GraphQL\Queries\Common\Branches\BaseBranchesQuery;

class BranchesQuery extends BaseBranchesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
