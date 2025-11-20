<?php

namespace App\Permissions\Musics;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'musics';

    public function getName(): string
    {
        return __('permissions.musics.group');
    }
}

