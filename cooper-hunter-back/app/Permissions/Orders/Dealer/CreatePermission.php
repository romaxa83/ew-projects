<?php

namespace App\Permissions\Orders\Dealer;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'dealer-order.create';

    public function getName(): string
    {
        return __('permissions.order.dealer.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
