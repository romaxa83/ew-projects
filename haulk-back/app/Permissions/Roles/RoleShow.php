<?php

namespace App\Permissions\Roles;

use App\Permissions\BasePermission;

class RoleShow extends BasePermission
{

    public const KEY = 'role.show';

    public function getName(): string
    {
        return __('permissions.role.grants.show');
    }

    public function getPosition(): int
    {
        return 20;
    }
}
