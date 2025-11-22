<?php

namespace App\Permissions\Warranty\WarrantyInfo;

use Core\Permissions\BasePermission;

class WarrantyInfoUpdatePermission extends BasePermission
{
    public const KEY = 'warranty_info.update';

    public function getName(): string
    {
        return __('permissions.warranty_info.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
