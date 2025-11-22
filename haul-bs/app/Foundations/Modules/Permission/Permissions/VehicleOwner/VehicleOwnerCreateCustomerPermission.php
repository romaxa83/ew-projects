<?php

namespace App\Foundations\Modules\Permission\Permissions\VehicleOwner;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class VehicleOwnerCreateCustomerPermission extends BasePermission
{
    public const KEY = VehicleOwnerPermissionsGroup::KEY . '.create-customer';
}
