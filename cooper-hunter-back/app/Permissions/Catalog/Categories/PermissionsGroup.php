<?php

namespace App\Permissions\Catalog\Categories;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.category';

    public function getName(): string
    {
        return __('permissions.catalog.category.group');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
