<?php

namespace App\Permissions\Commercial\Commissionings\Protocol;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.commissioning.protocol.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}

