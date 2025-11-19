<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Permissions;

use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Permission;
use Wezom\Core\Models\Permission\Role;

trait RoleHelperHelperTrait
{
    public function generateRole(
        string|RoleEnum|null $name = null,
        array $permissions = [],
        string $guard = 'graph_admin'
    ): Role {
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
