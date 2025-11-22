<?php

namespace Core\Chat\Permissions;

use Core\Permissions\BasePermission;

class ChatListPermission extends BasePermission
{
    public const KEY = ChatPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.chat.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
