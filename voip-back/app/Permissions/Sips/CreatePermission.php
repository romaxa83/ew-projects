<?php

namespace App\Permissions\Sips;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.sips.grants.create');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

