<?php

namespace App\Permissions\Warranty\WarrantyInfo;

use Core\Permissions\BasePermissionGroup;

class WarrantyInfoPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'warranty_info';

    public function getName(): string
    {
        return __('permissions.warranty_info.group');
    }

    public function getPosition(): int
    {
        return 72;
    }
}
