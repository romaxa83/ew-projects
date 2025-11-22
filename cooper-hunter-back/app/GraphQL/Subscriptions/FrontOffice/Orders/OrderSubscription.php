<?php

namespace App\GraphQL\Subscriptions\FrontOffice\Orders;

use App\GraphQL\Subscriptions\Common\Orders\BaseOrderSubscription;
use App\GraphQL\Subscriptions\FrontOffice\FrontOfficeBroadcaster;

class OrderSubscription extends BaseOrderSubscription
{
    use FrontOfficeBroadcaster;

    protected function setSubscriptionGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
