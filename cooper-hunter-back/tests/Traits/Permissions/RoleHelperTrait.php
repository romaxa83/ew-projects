<?php

namespace Tests\Traits\Permissions;

use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Models\Users\User;

trait RoleHelperTrait
{
    public function generateRole(string $name = null, array $permissions = [], string $guard = User::GUARD): Role
    {
        $attributes = [];
        if ($name) {
            $attributes = ['guard_name' => $guard, 'name' => $name];
        }

        $role = Role::factory()->create($attributes);

        $createdPermissions = collect();

        if ($permissions) {
            foreach ($permissions as $permission) {
                $createdPermissions->push(
                    Permission::findOrCreate($permission, $guard)
                );
            }
        } else {
            $createdPermissions->push(
                Permission::factory()->create()
            );
        }

        $role->syncPermissions(
            $createdPermissions->pluck('id')->toArray()
        );

        return $role;
    }

}
