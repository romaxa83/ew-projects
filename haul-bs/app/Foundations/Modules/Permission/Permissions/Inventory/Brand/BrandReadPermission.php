<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Brand;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class BrandReadPermission extends BasePermission
{
    public const KEY = BrandPermissionsGroup::KEY . '.read';
}
