<?php

namespace App\Permissions\Admins;

use App\Permissions\BasePermission;

class AdminList extends BasePermission
{
    public const KEY = 'admin.list';

    public function getName(): string
    {
        return __('permissions.admin.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
