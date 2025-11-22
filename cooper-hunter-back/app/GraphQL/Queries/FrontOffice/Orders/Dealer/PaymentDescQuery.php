<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\Common\Orders\Dealer\BasePaymentDescQuery;

class PaymentDescQuery extends BasePaymentDescQuery
{
    protected function setQueryGuard(): void
    {
        $this->setDealerGuard();
    }
}
