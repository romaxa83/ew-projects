<?php

namespace App\GraphQL\Subscriptions\BackOffice\SupportRequests;

use App\GraphQL\Subscriptions\BackOffice\BackOfficeBroadcaster;
use App\GraphQL\Subscriptions\Common\SupportRequests\BaseSupportRequestSubscription;

class SupportRequestSubscription extends BaseSupportRequestSubscription
{
    use BackOfficeBroadcaster;

    protected function setSubscriptionGuard(): void
    {
        $this->setAdminGuard();
    }
}
