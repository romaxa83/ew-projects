<?php

namespace App\Permissions\Alerts;

use Core\Permissions\BasePermission;

class AlertListPermission extends BasePermission
{
    public const KEY = 'alert.list';

    public function getName(): string
    {
        return __('permissions.alert.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
