<?php

namespace App\Permissions\Orders\DeliveryTypes;

use Core\Permissions\BasePermission;

class OrderDeliveryTypeUpdatePermission extends BasePermission
{

    public const KEY = 'order.delivery_type.update';

    public function getName(): string
    {
        return __('permissions.order.delivery_type.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
