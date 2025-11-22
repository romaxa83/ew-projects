<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Brand;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class BrandUpdatePermission extends BasePermission
{
    public const KEY = BrandPermissionsGroup::KEY . '.update';
}
