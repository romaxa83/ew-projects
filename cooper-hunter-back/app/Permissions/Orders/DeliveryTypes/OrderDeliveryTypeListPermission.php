<?php

namespace App\Permissions\Orders\DeliveryTypes;

use Core\Permissions\BasePermission;

class OrderDeliveryTypeListPermission extends BasePermission
{

    public const KEY = 'order.delivery_type.list';

    public function getName(): string
    {
        return __('permissions.order.delivery_type.grants.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
