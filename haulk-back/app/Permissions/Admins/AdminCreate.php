<?php

namespace App\Permissions\Admins;

use App\Permissions\BasePermission;

class AdminCreate extends BasePermission
{
    public const KEY = 'admin.create';

    public function getName(): string
    {
        return __('permissions.admin.grants.create');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
