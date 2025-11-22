<?php

namespace App\Permissions\Roles;

use App\Permissions\BasePermission;

class RoleUpdate extends BasePermission
{

    public const KEY = 'role.update';

    public function getName(): string
    {
        return __('permissions.role.grants.update');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
