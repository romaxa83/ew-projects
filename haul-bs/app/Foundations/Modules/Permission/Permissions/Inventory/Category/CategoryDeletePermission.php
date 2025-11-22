<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Category;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class CategoryDeletePermission extends BasePermission
{
    public const KEY = CategoryPermissionsGroup::KEY . '.delete';
}
