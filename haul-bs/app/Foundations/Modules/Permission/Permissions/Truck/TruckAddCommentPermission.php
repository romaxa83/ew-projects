<?php

namespace App\Foundations\Modules\Permission\Permissions\Truck;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TruckAddCommentPermission extends BasePermission
{
    public const KEY = TruckPermissionsGroup::KEY . '.add-comment';
}
