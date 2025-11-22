<?php

namespace App\Permissions\Tires;

use Core\Permissions\BasePermissionGroup;

class TirePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'tire';

    public function getName(): string
    {
        return __('permissions.tire.group');
    }

    public function getPosition(): int
    {
        return 0;
    }
}
