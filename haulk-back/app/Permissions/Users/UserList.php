<?php

namespace App\Permissions\Users;

use App\Permissions\BasePermission;

class UserList extends BasePermission
{
    public const KEY = 'user.list';

    public function getName(): string
    {
        return __('permissions.user.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
