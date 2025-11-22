<?php

namespace App\Permissions\About\ForMemberPages;

use Core\Permissions\BasePermission;

class ForMemberPageUpdatePermission extends BasePermission
{
    public const KEY = 'for_member_page.update';

    public function getName(): string
    {
        return __('permissions.for_member_page.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
