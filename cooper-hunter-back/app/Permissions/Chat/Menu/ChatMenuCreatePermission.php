<?php

namespace App\Permissions\Chat\Menu;

use Core\Permissions\BasePermission;

class ChatMenuCreatePermission extends BasePermission
{
    public const KEY = 'chat.menu.create';

    public function getName(): string
    {
        return __('permissions.chat.menu.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
