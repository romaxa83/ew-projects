<?php

namespace App\Foundations\Modules\Permission\Permissions\Trailer;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TrailerUpdatePermission extends BasePermission
{
    public const KEY = TrailerPermissionsGroup::KEY . '.update';
}
