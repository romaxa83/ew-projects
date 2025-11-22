<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Unit;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class UnitDeletePermission extends BasePermission
{
    public const KEY = UnitPermissionsGroup::KEY . '.delete';
}
