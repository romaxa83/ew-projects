<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerListPermission extends BasePermission
{
    public const KEY = 'dealer.list';

    public function getName(): string
    {
        return __('permissions.dealer.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}

