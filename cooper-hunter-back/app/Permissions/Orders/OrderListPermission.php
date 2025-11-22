<?php

namespace App\Permissions\Orders;

use Core\Permissions\BasePermission;

class OrderListPermission extends BasePermission
{

    public const KEY = 'order.list';

    public function getName(): string
    {
        return __('permissions.order.grants.list');
    }

    public function getPosition(): int
    {
        return 5;
    }
}
