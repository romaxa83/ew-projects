<?php

namespace App\Permissions\Users;

use App\Permissions\BasePermission;

class UserDelete extends BasePermission
{
    public const KEY = 'user.delete';

    public function getName(): string
    {
        return __('permissions.user.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
