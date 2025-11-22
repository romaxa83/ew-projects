<?php

namespace App\Permissions\Catalog\Products;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.product';

    public function getName(): string
    {
        return __('permissions.catalog.product.group');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
