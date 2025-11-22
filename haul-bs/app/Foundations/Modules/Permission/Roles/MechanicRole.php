<?php

namespace App\Foundations\Modules\Permission\Roles;

use App\Foundations\Modules\Permission\Models\Role as RoleModel;
use App\Foundations\Modules\Permission\Permissions;

final readonly class MechanicRole extends BaseRole
{
    public const NAME = 'mechanic';
    public const GUARD = RoleModel::GUARD_USER;

    public function getPermissions(): array
    {
        return [
            Permissions\Profile\ProfilePermissionsGroup::class => [
                Permissions\Profile\ProfileReadPermission::class,
                Permissions\Profile\ProfileUpdatePermission::class,
            ],
        ];
    }
}
