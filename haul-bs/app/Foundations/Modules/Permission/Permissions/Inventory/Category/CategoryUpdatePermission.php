<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Category;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class CategoryUpdatePermission extends BasePermission
{
    public const KEY = CategoryPermissionsGroup::KEY . '.update';
}
