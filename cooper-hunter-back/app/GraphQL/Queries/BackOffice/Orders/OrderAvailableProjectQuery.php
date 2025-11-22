<?php


namespace App\GraphQL\Queries\BackOffice\Orders;


use App\GraphQL\Queries\Common\Orders\BaseOrderAvailableProjectQuery;

class OrderAvailableProjectQuery extends BaseOrderAvailableProjectQuery
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
