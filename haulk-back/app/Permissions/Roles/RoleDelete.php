<?php

namespace App\Permissions\Roles;

use App\Permissions\BasePermission;

class RoleDelete extends BasePermission
{

    public const KEY = 'role.delete';

    public function getName(): string
    {
        return __('permissions.role.grants.delete');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
