<?php

namespace App\Permissions\Orders\DeliveryTypes;

use Core\Permissions\BasePermission;

class OrderDeliveryTypeDeletePermission extends BasePermission
{

    public const KEY = 'order.delivery_type.delete';

    public function getName(): string
    {
        return __('permissions.order.delivery_type.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
