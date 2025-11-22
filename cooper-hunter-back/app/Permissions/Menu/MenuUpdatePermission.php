<?php

namespace App\Permissions\Menu;

use Core\Permissions\BasePermission;

class MenuUpdatePermission extends BasePermission
{
    public const KEY = 'menu.update';

    public function getName(): string
    {
        return __('permissions.menu.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
