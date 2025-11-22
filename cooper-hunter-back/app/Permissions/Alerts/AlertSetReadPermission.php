<?php

namespace App\Permissions\Alerts;

use Core\Permissions\BasePermission;

class AlertSetReadPermission extends BasePermission
{
    public const KEY = 'alert.set_read';

    public function getName(): string
    {
        return __('permissions.alert.grants.set_read');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
