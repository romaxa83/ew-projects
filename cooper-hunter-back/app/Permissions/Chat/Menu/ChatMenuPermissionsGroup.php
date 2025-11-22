<?php

namespace App\Permissions\Chat\Menu;

use Core\Permissions\BasePermissionGroup;

class ChatMenuPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'chat.menu';

    public function getName(): string
    {
        return __('permissions.chat.menu.group');
    }

    public function getPosition(): int
    {
        return 90;
    }
}
