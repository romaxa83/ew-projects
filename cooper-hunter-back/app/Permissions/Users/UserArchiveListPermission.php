<?php

namespace App\Permissions\Users;

use Core\Permissions\BasePermission;

class UserArchiveListPermission extends BasePermission
{
    public const KEY = 'user.list-archive';

    public function getName(): string
    {
        return __('permissions.user.grants.list-archive');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
