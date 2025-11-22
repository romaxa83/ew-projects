<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Inventory;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class InventoryDeletePermission extends BasePermission
{
    public const KEY = InventoryPermissionsGroup::KEY . '.delete';
}
