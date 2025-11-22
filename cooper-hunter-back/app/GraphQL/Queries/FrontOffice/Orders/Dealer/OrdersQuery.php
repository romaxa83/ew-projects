<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\Common\Orders\Dealer\BaseOrdersQuery;
use App\Traits\GraphQL\Order\Dealer\InitArgsForFilter;

class OrdersQuery extends BaseOrdersQuery
{
    use InitArgsForFilter;

    protected function setQueryGuard(): void
    {
        $this->setDealerGuard();
    }

    protected function initArgs(array $args): array
    {
        return $this->init($args);
    }
}
