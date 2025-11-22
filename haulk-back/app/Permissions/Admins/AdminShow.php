<?php

namespace App\Permissions\Admins;

use App\Permissions\BasePermission;

class AdminShow extends BasePermission
{
    public const KEY = 'admin.show';

    public function getName(): string
    {
        return __('permissions.admin.grants.show');
    }

    public function getPosition(): int
    {
        return 20;
    }
}
