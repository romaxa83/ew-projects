<?php

namespace Database\Seeders;

use App\Models\Permissions\Role;
use Core\Services\Permissions\PermissionService;
use Illuminate\Database\Seeder;

abstract class BaseDefaultRoleSeeder extends Seeder
{
    public function clear(): void
    {
        Role::query()
            ->whereName($this->getRoleName())
            ->whereGuardName($this->getGuardName())
            ->delete();
    }

    abstract protected function getRoleName(): string;

    abstract protected function getGuardName(): string;

    protected function checkRolePermissions(Role $role): void
    {
        if (!$role->wasRecentlyCreated) {
            $this->getPermissionService()->seedAllPermissionsForGuard($role->guard_name);
            $this->getPermissionService()->syncAllPermissionForRoleAndGuard($role);
        }
    }

    protected function getPermissionService(): PermissionService
    {
        return app(PermissionService::class);
    }
}
