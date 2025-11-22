<?php

namespace App\Permissions\Orders;

use Core\Permissions\BasePermission;

class OrderUpdatePermission extends BasePermission
{

    public const KEY = 'order.update';

    public function getName(): string
    {
        return __('permissions.order.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
