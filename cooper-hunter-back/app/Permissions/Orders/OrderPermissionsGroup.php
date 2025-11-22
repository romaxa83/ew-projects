<?php

namespace App\Permissions\Orders;

use Core\Permissions\BasePermissionGroup;

class OrderPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'order';

    public function getName(): string
    {
        return __('permissions.order.group');
    }

    public function getPosition(): int
    {
        return 70;
    }
}
