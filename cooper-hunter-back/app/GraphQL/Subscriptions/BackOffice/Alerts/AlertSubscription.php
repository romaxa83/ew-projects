<?php

namespace App\GraphQL\Subscriptions\BackOffice\Alerts;

use App\GraphQL\Subscriptions\BackOffice\BackOfficeBroadcaster;
use App\GraphQL\Subscriptions\Common\Alerts\BaseAlertSubscription;

class AlertSubscription extends BaseAlertSubscription
{
    use BackOfficeBroadcaster;

    protected function setSubscriptionGuard(): void
    {
        $this->setAdminGuard();
    }
}
