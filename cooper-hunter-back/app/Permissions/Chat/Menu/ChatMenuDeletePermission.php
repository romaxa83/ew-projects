<?php

namespace App\Permissions\Chat\Menu;

use Core\Permissions\BasePermission;

class ChatMenuDeletePermission extends BasePermission
{
    public const KEY = 'chat.menu.delete';

    public function getName(): string
    {
        return __('permissions.chat.menu.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
