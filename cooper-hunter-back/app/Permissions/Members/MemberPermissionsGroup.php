<?php

namespace App\Permissions\Members;

use Core\Permissions\BasePermissionGroup;

class MemberPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'member';

    public function getName(): string
    {
        return __('permissions.member.group');
    }
}
