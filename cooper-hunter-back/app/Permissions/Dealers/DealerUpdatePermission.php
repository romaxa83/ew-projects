<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerUpdatePermission extends BasePermission
{
    public const KEY = 'dealer.update';

    public function getName(): string
    {
        return __('permissions.dealer.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

