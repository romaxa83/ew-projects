<?php

namespace App\Permissions\Stores\StoreCategories;

use Core\Permissions\BasePermission;

class StoreCategoryListPermission extends BasePermission
{
    public const KEY = StoreCategoryPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.store_category.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
