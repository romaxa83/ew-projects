<?php

namespace App\Permissions\Orders\Dealer;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'dealer-order.list';

    public function getName(): string
    {
        return __('permissions.order.dealer.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
