<?php

namespace Core\Chat\Permissions;

use Core\Permissions\BasePermission;

class ChatMessagingPermission extends BasePermission
{
    public const KEY = ChatPermissionGroup::KEY . '.messaging';

    public function getName(): string
    {
        return __('permissions.chat.grants.messaging');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
