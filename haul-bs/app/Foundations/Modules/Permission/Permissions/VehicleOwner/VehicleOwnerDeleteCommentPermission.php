<?php

namespace App\Foundations\Modules\Permission\Permissions\VehicleOwner;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class VehicleOwnerDeleteCommentPermission extends BasePermission
{
    public const KEY = VehicleOwnerPermissionsGroup::KEY . '.delete-comment';
}
