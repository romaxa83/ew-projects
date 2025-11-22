<?php

namespace App\Permissions\Menu;

use Core\Permissions\BasePermissionGroup;

class MenuPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'menu';

    public function getName(): string
    {
        return __('permissions.menu.group');
    }

    public function getPosition(): int
    {
        return 66;
    }
}
