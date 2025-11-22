<?php

namespace App\Permissions\Alerts;

use Core\Permissions\BasePermission;

class AlertSendPermission extends BasePermission
{
    public const KEY = 'alert.send';

    public function getName(): string
    {
        return __('permissions.alert.grants.send');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
