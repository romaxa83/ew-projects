<?php

namespace App\Foundations\Modules\Permission\Permissions\Role;

use App\Foundations\Modules\Permission\Permissions\BasePermission;
use App\Foundations\Modules\Permission\Roles\MechanicRole;

final readonly class RoleMechanicPermission extends BasePermission
{
    public const KEY = RolePermissionsGroup::KEY .'.'. MechanicRole::NAME;
}
