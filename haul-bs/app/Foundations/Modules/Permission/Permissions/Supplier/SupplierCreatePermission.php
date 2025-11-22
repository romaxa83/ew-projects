<?php

namespace App\Foundations\Modules\Permission\Permissions\Supplier;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class SupplierCreatePermission extends BasePermission
{
    public const KEY = SupplierPermissionsGroup::KEY . '.create';
}
