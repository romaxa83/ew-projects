<?php

namespace App\Permissions\Menu;

use Core\Permissions\BasePermission;

class MenuDeletePermission extends BasePermission
{
    public const KEY = 'menu.delete';

    public function getName(): string
    {
        return __('permissions.menu.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
