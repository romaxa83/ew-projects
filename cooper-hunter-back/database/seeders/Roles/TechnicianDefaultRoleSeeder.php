<?php

namespace Database\Seeders\Roles;

use App\Models\Permissions\Role;
use App\Models\Technicians\Technician;
use Core\Services\Permissions\PermissionService;
use Illuminate\Database\Seeder;
use Throwable;

class TechnicianDefaultRoleSeeder extends Seeder
{
    /** @throws Throwable */
    public function run(): void
    {
        makeTransaction(
            function () {
                $role = $this->getPermissionService()->firstOrCreateDefaultTechnicianRole();

                if (!$role->wasRecentlyCreated) {
                    $this->getPermissionService()->seedAllPermissionsForGuard($role->guard_name);
                    $this->getPermissionService()->syncAllPermissionForRoleAndGuard($role);
                }
            }
        );
    }

    public function getPermissionService(): PermissionService
    {
        return app(PermissionService::class);
    }

    public function clear(): void
    {
        Role::query()
            ->whereName(config('permission.roles.technician'))
            ->whereGuardName(Technician::GUARD)
            ->delete();
    }
}
