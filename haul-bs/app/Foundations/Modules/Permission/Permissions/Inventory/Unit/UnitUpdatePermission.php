<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Unit;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class UnitUpdatePermission extends BasePermission
{
    public const KEY = UnitPermissionsGroup::KEY . '.update';
}
