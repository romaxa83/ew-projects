<?php

namespace App\Permissions\Musics;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.musics.grants.create');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
