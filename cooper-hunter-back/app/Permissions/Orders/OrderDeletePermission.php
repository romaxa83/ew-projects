<?php

namespace App\Permissions\Orders;

use Core\Permissions\BasePermission;

class OrderDeletePermission extends BasePermission
{

    public const KEY = 'order.delete';

    public function getName(): string
    {
        return __('permissions.order.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
