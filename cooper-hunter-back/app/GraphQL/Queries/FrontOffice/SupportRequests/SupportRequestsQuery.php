<?php


namespace App\GraphQL\Queries\FrontOffice\SupportRequests;


use App\GraphQL\Queries\Common\SupportRequests\BaseSupportRequestsQuery;

class SupportRequestsQuery extends BaseSupportRequestsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
