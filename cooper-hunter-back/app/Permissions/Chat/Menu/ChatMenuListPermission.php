<?php

namespace App\Permissions\Chat\Menu;

use Core\Permissions\BasePermission;

class ChatMenuListPermission extends BasePermission
{
    public const KEY = 'chat.menu.list';

    public function getName(): string
    {
        return __('permissions.chat.menu.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
