<?php

namespace App\Permissions\Admins;

use Core\Permissions\BasePermission;

class AdminLoginAsUserPermission extends BasePermission
{
    public const KEY = 'admin.login_as_user';

    public function getName(): string
    {
        return __('permissions.admin-actions.grants.login-as-user');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
