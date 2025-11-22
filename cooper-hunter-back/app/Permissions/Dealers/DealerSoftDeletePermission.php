<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerSoftDeletePermission extends BasePermission
{
    public const KEY = 'dealer.delete.soft';

    public function getName(): string
    {
        return __('permissions.dealer.grants.delete-soft');
    }

    public function getPosition(): int
    {
        return 4;
    }
}

