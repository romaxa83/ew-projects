<?php

namespace Core\Chat\Permissions;

use Core\Permissions\BasePermissionGroup;

class ChatPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'chat';

    public function getName(): string
    {
        return __('permissions.chat.group');
    }

    public function getPosition(): int
    {
        return 1100;
    }
}
