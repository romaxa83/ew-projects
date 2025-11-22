<?php

namespace App\Permissions\Orders\DeliveryTypes;

use Core\Permissions\BasePermission;

class OrderDeliveryTypeCreatePermission extends BasePermission
{

    public const KEY = 'order.delivery_type.create';

    public function getName(): string
    {
        return __('permissions.order.delivery_type.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
