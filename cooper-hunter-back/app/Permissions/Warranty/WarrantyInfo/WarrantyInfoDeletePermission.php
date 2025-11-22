<?php

namespace App\Permissions\Warranty\WarrantyInfo;

use Core\Permissions\BasePermission;

class WarrantyInfoDeletePermission extends BasePermission
{
    public const KEY = 'warranty_info.delete';

    public function getName(): string
    {
        return __('permissions.warranty_info.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
