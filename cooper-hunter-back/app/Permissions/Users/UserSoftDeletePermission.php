<?php

namespace App\Permissions\Users;

use Core\Permissions\BasePermission;

class UserSoftDeletePermission extends BasePermission
{
    public const KEY = 'user.delete.soft';

    public function getName(): string
    {
        return __('permissions.user.grants.delete-soft');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
