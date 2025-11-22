<?php

namespace App\Permissions\Orders\DeliveryTypes;

use Core\Permissions\BasePermissionGroup;

class OrderDeliveryTypePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'order.delivery_type';

    public function getName(): string
    {
        return __('permissions.order.delivery_type.group');
    }

    public function getPosition(): int
    {
        return 70;
    }
}
