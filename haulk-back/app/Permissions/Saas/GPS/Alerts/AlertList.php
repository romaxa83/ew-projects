<?php

namespace App\Permissions\Saas\GPS\Alerts;

use App\Permissions\BasePermission;

class AlertList extends BasePermission
{
    public const KEY = 'gps-alert.list';

    public function getName(): string
    {
        return __('permissions.gps-alert.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}

