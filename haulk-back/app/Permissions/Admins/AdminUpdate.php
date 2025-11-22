<?php

namespace App\Permissions\Admins;

use App\Permissions\BasePermission;

class AdminUpdate extends BasePermission
{

    public const KEY = 'admin.update';

    public function getName(): string
    {
        return __('permissions.admin.grants.update');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
