<?php

namespace App\Traits\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Permission as PermissionModel;
use App\Models\Permissions\Role;
use Core\Services\Permissions\PermissionFilterService;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Contracts\Permission;

/**
 * @see HasRoles::getRoleAttribute()
 * @property-read Permission[]|Collection permissions
 *
 * @see HasRoles::getRoleAttribute()
 * @property-read null|Role $role
 *
 * @see HasRoles::roles()
 * @property-read Collection|Role[] $roles
 */
trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(
            config('permission.roles.super_admin'),
            Admin::GUARD
        );
    }

    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $this->getPermissionFilterService()
            ->filter($this, $permissions)
            ->sort()
            ->values();
    }

    protected function getPermissionFilterService(): PermissionFilterService
    {
        return app(PermissionFilterService::class);
    }

    protected function hasPermissionViaRole(Permission|PermissionModel $permission): bool
    {
        $filteredPermissions = $this->getPermissionFilterService()
            ->filter($this, collect([$permission]));

        if ($filteredPermissions->isEmpty()) {
            return false;
        }

        return $this->hasRole($permission->roles);
    }

    protected function hasDirectPermission(Permission $permission): bool
    {
        return false;
    }
}
