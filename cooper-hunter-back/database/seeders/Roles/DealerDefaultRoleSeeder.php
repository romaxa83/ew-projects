<?php

namespace Database\Seeders\Roles;

use App\Models\Dealers\Dealer;
use App\Models\Permissions\Role;
use Core\Services\Permissions\PermissionService;
use Illuminate\Database\Seeder;
use Throwable;

class DealerDefaultRoleSeeder extends Seeder
{
    /** @throws Throwable */
    public function run(): void
    {
        makeTransaction(
            function () {
                $role = $this->getPermissionService()->firstOrCreateDefaultDealerRole();

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
            ->whereName(config('permission.roles.dealer'))
            ->whereGuardName(Dealer::GUARD)
            ->delete();
    }
}

