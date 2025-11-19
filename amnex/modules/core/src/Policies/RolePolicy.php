<?php

namespace Wezom\Core\Policies;

use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;

class RolePolicy
{
    public function update(Admin $admin, Role $role): bool
    {
        return $admin->can('roles.update') && !in_array($role->system_type, RoleEnum::values());
    }

    public function delete(Admin $admin, Role $role): bool
    {
        return $admin->can('roles.update') && !in_array($role->system_type, RoleEnum::values());
    }
}
