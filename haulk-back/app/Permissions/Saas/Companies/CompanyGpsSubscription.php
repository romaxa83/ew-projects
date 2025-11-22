<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyGpsSubscription extends BasePermission
{
    public const KEY = 'company.gpd_subscriptions';

    public function getName(): string
    {
        return __('permissions.company.grants.gpd_subscriptions');
    }

    public function getPosition(): int
    {
        return 25;
    }
}

