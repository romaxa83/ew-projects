<?php

namespace Wezom\Admins\Database\Seeders;

use Illuminate\Database\Seeder;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Contracts\Permissions\PermissionGroup;
use Wezom\Core\Models\Permission\Permission as PermissionModel;
use Wezom\Core\Permissions\Permission;
use Wezom\Core\Permissions\PermissionsManager;

class AdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        PermissionModel::query()->upsert(
            app(PermissionsManager::class)->guard(Admin::GUARD)->getAll()
                ->flatMap(fn (PermissionGroup $group) => $group->getPermissions()->map(
                    fn (Permission $permission) => [
                        'name' => $permission->getKey(),
                        'guard_name' => Admin::GUARD,
                    ]
                ))->toArray(),
            ['name', 'guard_name']
        );
    }
}
