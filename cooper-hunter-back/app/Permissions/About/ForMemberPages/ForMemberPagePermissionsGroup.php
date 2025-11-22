<?php

namespace App\Permissions\About\ForMemberPages;

use Core\Permissions\BasePermissionGroup;

class ForMemberPagePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'for_member_page';

    public function getName(): string
    {
        return __('permissions.for_member_page.group');
    }

    public function getPosition(): int
    {
        return 75;
    }
}
