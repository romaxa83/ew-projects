<?php

namespace App\Foundations\Modules\Permission\Permissions\Role;

use App\Foundations\Modules\Permission\Permissions\BasePermission;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Foundations\Modules\Permission\Roles\SalesManagerRole;

final readonly class RoleSalesManagerPermission extends BasePermission
{
    public const KEY = RolePermissionsGroup::KEY .'.'. SalesManagerRole::NAME;
}
