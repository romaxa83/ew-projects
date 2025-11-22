<?php

namespace App\Permissions\Commercial\Commissionings\Protocol;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.commissioning.protocol.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
