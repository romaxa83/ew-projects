<?php

namespace App\Services\Permissions;

use App\Dto\Permissions\RoleDto;
use App\Models\Permissions\Permission as PermissionModel;
use App\Models\Permissions\Role;
use App\Permissions\Permission;
use App\Permissions\PermissionGroup;
use Closure;
use DB;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Throwable;

class PermissionService
{

    private Collection $guards;

    public function __construct()
    {
        $this->guards = Collection::make();
    }

    /**
     * @param RoleDto $roleDto
     * @param string $guard
     * @return Role
     * @throws Throwable
     */
    public function createRole(RoleDto $roleDto, string $guard): Role
    {
        try {
            DB::beginTransaction();

            $role = new Role();
            $role->name = $roleDto->getName();
            $role->guard_name = $guard;
            $role->save();

            $permissions = $this->sync($roleDto->getPermissions(), $guard);

            $role->syncPermissions($permissions);

            DB::commit();

            return $role;
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error($exception);

            throw $exception;
        }
    }

    public function sync(array $permissionList, string $guard): array
    {
        $permissionFilteredList = $this->filterByExistsPermissions($permissionList, $guard);

        $permission = array_map(
            $this->mapPermissionFromString($guard),
            $permissionFilteredList
        );

        PermissionModel::query()->upsert($permission, ['name', 'guard_name']);

        return $permissionFilteredList;
    }

    public function filterByExistsPermissions(array $permissionList, string $guard): array
    {
        $availablePermissions = $this->getFlattenPermissions($guard)
            ->map(fn(Permission $permission) => $permission->getKey())
            ->toArray();

        return array_intersect($availablePermissions, $permissionList);
    }

    public function getFlattenPermissions(string $guard): Collection
    {
        return $this->getGroupsFor($guard)
            ->map(fn(PermissionGroup $group) => $group->getPermissions())
            ->flatten();
    }

    public function getGroupsFor(string $guard): Collection
    {
        if (!$this->guards->has($guard)) {
            $this->guards->put($guard, $this->buildGroupsFor($guard));
        }

        return $this->guards->get($guard);
    }

    protected function buildGroupsFor(string $guard): Collection
    {
        $result = Collection::make();

        foreach (config("grants.$guard.groups") as $groupClass => $permissionsClasses) {
            /** @var PermissionGroup $group */
            $group = new $groupClass(
                array_map(static fn(string $className) => new $className(), $permissionsClasses)
            );

            $result->put($group->getKey(), $group);
        }

        return $result;
    }

    protected function mapPermissionFromString(string $guard): Closure
    {
        return static function (string $permission) use ($guard) {
            return [
                'name' => $permission,
                'guard_name' => $guard,
            ];
        };
    }

    /**
     * @param Role $role
     * @param RoleDto $roleDto
     * @param string $guard
     * @return Role
     * @throws Throwable
     */
    public function updateRole(Role $role, RoleDto $roleDto, string $guard): Role
    {
        try {
            DB::beginTransaction();

            $role->name = $roleDto->getName();
            $role->save();

            $permissions = $this->sync($roleDto->getPermissions(), $guard);

            $role->syncPermissions($permissions);

            DB::commit();

            return $role;
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error($exception);

            throw $exception;
        }
    }

    /**
     * @param Role $role
     * @return bool
     * @throws Exception
     */
    public function deleteRole(Role $role): bool
    {
        return $role->delete();
    }
}
