<?php

namespace App\Permissions\Stores\StoreCategories;

use Core\Permissions\BasePermissionGroup;

class StoreCategoryPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'store_category';

    public function getName(): string
    {
        return __('permissions.store_category.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
