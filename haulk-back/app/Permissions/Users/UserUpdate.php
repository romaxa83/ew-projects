<?php

namespace App\Permissions\Users;

use App\Permissions\BasePermission;

class UserUpdate extends BasePermission
{
    public const KEY = 'user.update';

    public function getName(): string
    {
        return __('permissions.user.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
