<?php

namespace Wezom\Core\Services;

use Wezom\Core\Dto\RoleDto;
use Wezom\Core\Models\Permission\Role;

class RoleService
{
    public function create(RoleDto $roleDto, string $guard): Role
    {
        $role = new Role();
        $role->name = $roleDto->getName();
        $role->note = $roleDto->getNote();
        $role->system_type = $roleDto->getSystemType();
        $role->guard_name = $guard;
        $role->active = true;
        $role->save();

        $role->syncPermissions($roleDto->getPermissions());

        return $role;
    }

    public function update(Role $role, RoleDto $roleDto, string $guard): Role
    {
        $role->guard_name = $guard;
        $role->name = $roleDto->getName();
        $role->note = $roleDto->getNote();
        $role->system_type = $roleDto->getSystemType();
        $role->save();

        $role->syncPermissions($roleDto->getPermissions());

        return $role;
    }
}
