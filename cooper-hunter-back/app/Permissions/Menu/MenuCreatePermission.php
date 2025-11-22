<?php

namespace App\Permissions\Menu;

use Core\Permissions\BasePermission;

class MenuCreatePermission extends BasePermission
{
    public const KEY = 'menu.create';

    public function getName(): string
    {
        return __('permissions.menu.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
