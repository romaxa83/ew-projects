<?php

namespace App\Permissions\Orders\Dealer;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'dealer-order';

    public function getName(): string
    {
        return __('permissions.order.dealer.group');
    }

    public function getPosition(): int
    {
        return 70;
    }
}
