<?php

namespace App\GraphQL\Subscriptions\FrontOffice\Alerts;

use App\GraphQL\Subscriptions\Common\Alerts\BaseAlertSubscription;
use App\GraphQL\Subscriptions\FrontOffice\FrontOfficeBroadcaster;

class AlertSubscription extends BaseAlertSubscription
{
    use FrontOfficeBroadcaster;

    protected function setSubscriptionGuard(): void
    {
        $this->setMemberGuard();
    }
}
