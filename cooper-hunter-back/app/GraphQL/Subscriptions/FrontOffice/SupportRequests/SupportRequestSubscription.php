<?php

namespace App\GraphQL\Subscriptions\FrontOffice\SupportRequests;

use App\GraphQL\Subscriptions\Common\SupportRequests\BaseSupportRequestSubscription;
use App\GraphQL\Subscriptions\FrontOffice\FrontOfficeBroadcaster;

class SupportRequestSubscription extends BaseSupportRequestSubscription
{
    use FrontOfficeBroadcaster;

    protected function setSubscriptionGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
