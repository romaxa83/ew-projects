<?php

namespace App\GraphQL\Subscriptions\BackOffice\Orders;

use App\GraphQL\Subscriptions\BackOffice\BackOfficeBroadcaster;
use App\GraphQL\Subscriptions\Common\Orders\BaseOrderSubscription;

class OrderSubscription extends BaseOrderSubscription
{
    use BackOfficeBroadcaster;

    protected function setSubscriptionGuard(): void
    {
        $this->setAdminGuard();
    }
}
