<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerRestorePermission extends BasePermission
{
    public const KEY = 'dealer.restore';

    public function getName(): string
    {
        return __('permissions.dealer.grants.restore');
    }

    public function getPosition(): int
    {
        return 4;
    }
}


