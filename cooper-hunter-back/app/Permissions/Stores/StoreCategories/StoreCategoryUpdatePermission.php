<?php

namespace App\Permissions\Stores\StoreCategories;

use Core\Permissions\BasePermission;

class StoreCategoryUpdatePermission extends BasePermission
{
    public const KEY = StoreCategoryPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.store_category.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
