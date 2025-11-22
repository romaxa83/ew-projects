<?php

namespace App\Permissions\Chat\Menu;

use Core\Permissions\BasePermission;

class ChatMenuUpdatePermission extends BasePermission
{
    public const KEY = 'chat.menu.update';

    public function getName(): string
    {
        return __('permissions.chat.menu.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
