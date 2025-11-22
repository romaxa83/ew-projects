<?php


namespace App\GraphQL\Queries\FrontOffice\SupportRequests;


use App\GraphQL\Queries\Common\SupportRequests\BaseSupportRequestCounterQuery;

class SupportRequestCounterQuery extends BaseSupportRequestCounterQuery
{
    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
