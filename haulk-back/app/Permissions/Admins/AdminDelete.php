<?php

namespace App\Permissions\Admins;

use App\Permissions\BasePermission;

class AdminDelete extends BasePermission
{

    public const KEY = 'admin.delete';

    public function getName(): string
    {
        return __('permissions.admin.grants.delete');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
