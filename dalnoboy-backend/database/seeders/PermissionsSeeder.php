<?php

namespace Database\Seeders;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use Core\Services\Permissions\PermissionService;
use Illuminate\Database\Seeder;
use Throwable;

class PermissionsSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        makeTransaction(
            static fn() => PermissionService::seedRoles()
        );
    }

    public function clear(): void
    {
        Role::query()
            ->whereName(config('permission.roles.super_admin'))
            ->whereGuardName(Admin::GUARD)
            ->delete();
    }
}
