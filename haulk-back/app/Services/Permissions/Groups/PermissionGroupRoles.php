<?php

namespace App\Services\Permissions\Groups;

use App\Models\Users\User;

class PermissionGroupRoles extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'roles';
    }

    public function getPermissions(): array
    {
        return [
            'superadmin',
            'admin',
            'dispatcher',
            'accountant',
            'driver',
            strtolower(User::BSSUPERADMIN_ROLE),
            strtolower(User::BSADMIN_ROLE),
            strtolower(User::BSMECHANIC_ROLE),
            strtolower(User::OWNER_ROLE),
            strtolower(User::OWNER_DRIVER_ROLE),
        ];
    }
}
