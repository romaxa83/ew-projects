<?php

namespace App\Permissions\Stores\StoreCategories;

use Core\Permissions\BasePermission;

class StoreCategoryCreatePermission extends BasePermission
{
    public const KEY = StoreCategoryPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.store_category.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
