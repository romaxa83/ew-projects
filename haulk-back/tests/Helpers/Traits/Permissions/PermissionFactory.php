<?php

namespace Tests\Helpers\Traits\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Permissions\Admins\AdminCreate;
use App\Permissions\Admins\AdminDelete;
use App\Permissions\Admins\AdminList;
use App\Permissions\Admins\AdminShow;
use App\Permissions\Admins\AdminUpdate;
use App\Permissions\Roles\RoleCreate;
use App\Permissions\Roles\RoleDelete;
use App\Permissions\Roles\RoleList;
use App\Permissions\Roles\RoleShow;
use App\Permissions\Roles\RoleUpdate;
use App\Permissions\Saas\GPS\Devices\DeviceCreate;

trait PermissionFactory
{

    protected function createRoleAdminCreator(): Role
    {
        return $this->createRole('Admin creator', [AdminCreate::KEY], Admin::GUARD);
    }

    protected function createRole(string $name = null, array $permissions = [], string $guard = User::GUARD): Role
    {
        $attributes = [];

        if ($name) {
            $attributes = [
                'name' => $name,
                'guard_name' => $guard
            ];
        }

        $role = factory(Role::class)->create($attributes);

        $createdPermission = collect();

        if ($permissions) {
            foreach ($permissions as $permission) {
                $createdPermission->push(
                    Permission::findOrCreate($permission, $guard)
                );
            }
        } else {
            $createdPermission->push(
                factory(Permission::class)->create()
            );
        }

        $role->syncPermissions(
            $createdPermission->pluck('id')->toArray()
        );

        return $role;
    }

    protected function createRoleAdminUpdater(): Role
    {
        return $this->createRole('Admin creator', [AdminUpdate::KEY], Admin::GUARD);
    }

    protected function createRoleAdminDeleter(): Role
    {
        return $this->createRole('Admin creator', [AdminDelete::KEY], Admin::GUARD);
    }

    protected function createRoleAdminLister(): Role
    {
        return $this->createRole('Admin creator', [AdminList::KEY], Admin::GUARD);
    }

    protected function createRoleAdminManager(): Role
    {
        return $this->createRole(
            'Admin creator',
            [
                AdminCreate::KEY,
                AdminUpdate::KEY,
                AdminList::KEY,
                AdminShow::KEY,
                AdminDelete::KEY,
            ],
            Admin::GUARD
        );
    }

    protected function createRoleManagerRole(): Role
    {
        return $this->createRole(
            'Role manager',
            [
                RoleCreate::KEY,
                RoleUpdate::KEY,
                RoleDelete::KEY,
                RoleShow::KEY,
                RoleList::KEY,
            ],
            Admin::GUARD
        );
    }

    protected function createRoleWithRoleListPermission(): Role
    {
        return $this->createRole('Role lister', [RoleList::KEY], Admin::GUARD);
    }

    protected function createRoleDeviceCreator(): Role
    {
        return $this->createRole('Device creator', [DeviceCreate::KEY], Admin::GUARD);
    }

}
