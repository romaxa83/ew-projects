<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerDeletePermission extends BasePermission
{
    public const KEY = 'dealer.delete';

    public function getName(): string
    {
        return __('permissions.dealer.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}

