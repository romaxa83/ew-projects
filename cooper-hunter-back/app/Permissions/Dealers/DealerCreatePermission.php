<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerCreatePermission extends BasePermission
{
    public const KEY = 'dealer.create';

    public function getName(): string
    {
        return __('permissions.dealer.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}

