<?php

namespace App\Foundations\Modules\Permission\Permissions\Role;

use App\Foundations\Modules\Permission\Permissions\BasePermission;
use App\Foundations\Modules\Permission\Roles\AdminRole;

final readonly class RoleAdminPermission extends BasePermission
{
    public const KEY = RolePermissionsGroup::KEY .'.'. AdminRole::NAME;
}
