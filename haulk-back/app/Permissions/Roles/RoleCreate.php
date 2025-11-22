<?php

namespace App\Permissions\Roles;

use App\Permissions\BasePermission;

class RoleCreate extends BasePermission
{

    public const KEY = 'role.create';

    public function getName(): string
    {
        return __('permissions.role.grants.create');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
