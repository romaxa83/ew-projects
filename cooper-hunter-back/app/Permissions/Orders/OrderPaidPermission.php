<?php

namespace App\Permissions\Orders;

use Core\Permissions\BasePermission;

class OrderPaidPermission extends BasePermission
{

    public const KEY = 'order.paid';

    public function getName(): string
    {
        return __('permissions.order.grants.paid');
    }

    public function getPosition(): int
    {
        return 5;
    }
}
