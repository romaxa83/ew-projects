<?php


namespace App\GraphQL\Queries\BackOffice\SupportRequests;


use App\GraphQL\Queries\Common\SupportRequests\BaseSupportRequestCounterQuery;

class SupportRequestCounterQuery extends BaseSupportRequestCounterQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
