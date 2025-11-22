<?php

namespace App\Permissions\Stores\StoreCategories;

use Core\Permissions\BasePermission;

class StoreCategoryDeletePermission extends BasePermission
{
    public const KEY = StoreCategoryPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.store_category.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
