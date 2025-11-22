<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Inventory;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class InventoryUpdatePermission extends BasePermission
{
    public const KEY = InventoryPermissionsGroup::KEY . '.update';
}
