<?php

namespace App\Foundations\Modules\Permission\Permissions\VehicleOwner;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class VehicleOwnerCreatePermission extends BasePermission
{
    public const KEY = VehicleOwnerPermissionsGroup::KEY . '.create';
}
