<?php

namespace App\Permissions\Orders\Dealer;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'dealer-order.delete';

    public function getName(): string
    {
        return __('permissions.order.dealer.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}

