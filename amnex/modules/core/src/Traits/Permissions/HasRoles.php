<?php

namespace Wezom\Core\Traits\Permissions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;

/**
 * @see HasRoles::getRoleAttribute()
 *
 * @property Permission[]|Collection $permissions
 *
 * @see HasRoles::getRoleAttribute()
 *
 * @property null|Role $role
 *
 * @see HasRoles::roles()
 *
 * @property Collection|Role[] $roles
 *
 * @see self::scopeWhereRoles()
 *
 * @method Builder|static whereRoles(array|Collection|Role|string|RoleEnum $roles, string $guard = null)
 */
trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }

    public function roles(): BelongsToMany
    {
        $relation = $this->morphToMany(
            Role::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotRole
        );

        if (!app(PermissionRegistrar::class)->teams) {
            return $relation;
        }

        $teamField = config('permission.table_names.roles') . '.' . app(PermissionRegistrar::class)->teamsKey;

        return $relation->wherePivot(app(PermissionRegistrar::class)->teamsKey, getPermissionsTeamId())
            ->where(fn ($q) => $q->whereNull($teamField)->orWhere($teamField, getPermissionsTeamId()));
    }

    protected function hasDirectPermission(Permission $permission): bool
    {
        return false;
    }

    /**
     * Scope the model query to certain roles only.
     */
    public function scopeWhereRoles(Builder $query, RoleEnum|array|string $roles, ?string $guard = null): Builder
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $guard = $guard ?: $this->getDefaultGuardName();
        $roleClass = $this->getRoleClass();
        /** @var Collection $rolesResult */
        $rolesResult = (new $roleClass())->whereGuardName($guard)->whereType($roles)->get();

        return $query->whereHas('roles', function (Builder $subQuery) use ($rolesResult) {
            $subQuery->whereIn(config('permission.table_names.roles') . '.id', $rolesResult->pluck('id')->values());
        });
    }
}
