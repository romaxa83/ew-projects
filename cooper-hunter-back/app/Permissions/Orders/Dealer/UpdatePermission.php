<?php

namespace App\Permissions\Orders\Dealer;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'dealer-order.update';

    public function getName(): string
    {
        return __('permissions.order.dealer.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
