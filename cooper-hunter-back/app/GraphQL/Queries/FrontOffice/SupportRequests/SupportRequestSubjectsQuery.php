<?php


namespace App\GraphQL\Queries\FrontOffice\SupportRequests;


use App\GraphQL\Queries\Common\SupportRequests\BaseSupportRequestSubjectsQuery;

class SupportRequestSubjectsQuery extends BaseSupportRequestSubjectsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
