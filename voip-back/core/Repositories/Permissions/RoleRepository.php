<?php

namespace Core\Repositories\Permissions;

use App\Models\Permissions\Role;

final class RoleRepository
{
    public function getByNameAndGuard(string $name, string $guard): ?Role
    {
        return Role::query()
            ->whereName($name)
            ->whereGuardName($guard)
            ->first();
    }
}
