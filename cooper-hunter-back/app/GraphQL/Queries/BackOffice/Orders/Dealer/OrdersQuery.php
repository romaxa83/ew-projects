<?php

namespace App\GraphQL\Queries\BackOffice\Orders\Dealer;

use App\GraphQL\Queries\Common\Orders\Dealer\BaseOrdersQuery;

class OrdersQuery extends BaseOrdersQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
