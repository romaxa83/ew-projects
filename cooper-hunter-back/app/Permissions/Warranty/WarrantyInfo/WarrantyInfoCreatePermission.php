<?php

namespace App\Permissions\Warranty\WarrantyInfo;

use Core\Permissions\BasePermission;

class WarrantyInfoCreatePermission extends BasePermission
{
    public const KEY = 'warranty_info.create';

    public function getName(): string
    {
        return __('permissions.warranty_info.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
