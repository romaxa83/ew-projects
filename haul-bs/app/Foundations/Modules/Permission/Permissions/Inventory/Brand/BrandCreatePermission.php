<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Brand;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class BrandCreatePermission extends BasePermission
{
    public const KEY = BrandPermissionsGroup::KEY . '.create';
}
