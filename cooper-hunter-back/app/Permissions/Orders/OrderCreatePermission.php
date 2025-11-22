<?php

namespace App\Permissions\Orders;

use Core\Permissions\BasePermission;

class OrderCreatePermission extends BasePermission
{

    public const KEY = 'order.create';

    public function getName(): string
    {
        return __('permissions.order.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
