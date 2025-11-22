<?php

namespace App\Foundations\Modules\Permission\Permissions\Supplier;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class SupplierUpdatePermission extends BasePermission
{
    public const KEY = SupplierPermissionsGroup::KEY . '.update';
}
