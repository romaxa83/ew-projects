<?php

namespace Core\Services\Permissions;

use App\Dto\Permission\RoleDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\Permissions\RoleForOwnerException;
use App\Models\Localization\Language;
use App\Models\Permissions\Permission as PermissionModel;
use App\Models\Permissions\Role;
use App\Models\Permissions\RoleTranslate;
use Core\Permissions\BasePermission;
use Core\Permissions\Permission;
use Core\Permissions\PermissionGroup;
use Illuminate\Support\Collection;

class PermissionService
{
    private Collection $guards;

    public function __construct()
    {
        $this->guards = Collection::make();
    }

    public function createRole(RoleDto $roleDto, string $guard): Role
    {
        $role = new Role();
        $role->name = $roleDto->getName();
        $role->guard_name = $guard;
        $role->save();

        foreach ($roleDto->getTranslates() as $translate) {
            $roleTranslate = new RoleTranslate();
            $roleTranslate->row_id = $role->id;
            $roleTranslate->language = $translate->getLanguage();
            $roleTranslate->title = $translate->getTitle();
            $roleTranslate->save();
        }

        $permissions = $this->sync($roleDto->getPermissions(), $guard);

        $role->syncPermissions($permissions);

        return $role;
    }

    public function sync(array $permissionList, string $guard): array
    {
        $permissionFilteredList = $this->filterByExistsPermissions($permissionList, $guard);

        $permission = array_map(
            static fn(string $permission) => [
                'name' => $permission,
                'guard_name' => $guard,
            ],
            $permissionFilteredList
        );

        PermissionModel::query()->upsert($permission, ['name', 'guard_name']);

        return $permissionFilteredList;
    }

    public function filterByExistsPermissions(array $permissionList, string $guard): array
    {
        $availablePermissions = $this->getPermissionsList($guard)
            ->map(fn(Permission $permission) => $permission->getKey())
            ->toArray();

        return array_intersect($availablePermissions, $permissionList);
    }

    /**
     * @param string $guard
     * @return Collection|Permission[]
     */
    public function getPermissionsList(string $guard): Collection|array
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

        foreach (config("grants.matrix.$guard.groups") as $groupClass => $permissionsClasses) {
            /** @var PermissionGroup $group */
            $group = new $groupClass(
                array_map(static fn(string $className) => new $className(), $permissionsClasses)
            );

            $result->put($group->getKey(), $group);
        }

        return $result;
    }

    public function updateRole(Role $role, RoleDto $roleDto, string $guard): Role
    {
        $role->guard_name = $guard;
        $role->name = $roleDto->getName();
        $role->save();

        foreach ($roleDto->getTranslates() as $translate) {
            /** @var RoleTranslate $roleTranslate */
            $roleTranslate = $role->translates->where('language', $translate->getLanguage())->first();
            $roleTranslate->title = $translate->getTitle();
            $roleTranslate->save();
        }

        $permissions = $this->sync($roleDto->getPermissions(), $guard);

        $role->syncPermissions($permissions);

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->isForOwner()) {
            throw new RoleForOwnerException(__('exceptions.roles.cant-delete-role-for-owner'));
        }

        $role->permissions()->detach();
        $role->translates()->delete();
        $role->delete();

        return true;
    }

    public static function seedRoles(): void
    {
        $guards = GuardsEnum::getInstances();

        foreach ($guards as $guard) {
            $roles = $guard->getRoles();

            foreach ($roles as $role) {
                $roleModel = Role::updateOrCreate(
                    [
                        'name' => $role->value,
                    ],
                    [
                        'guard_name' => $guard->value,
                    ]
                );
                languages()
                    ->each(
                        fn(Language $language) => $roleModel
                            ->translates()
                            ->updateOrCreate(
                                [
                                    'language' => $language->slug
                                ],
                                [
                                    'title' => $role->getTranslation($language->slug)
                                ]
                            )
                    );

                $permissionsGroups = $guard->getPermissions();
                $syncPermissions = [];

                /**@var BasePermission $permission */
                foreach ($permissionsGroups as $group => $permissions) {
                    foreach ($permissions as $permission) {
                        $permissionModel = PermissionModel::firstOrCreate(
                            [
                                'guard_name' => $guard->value,
                                'name' => $permission::KEY
                            ]
                        );

                        if (!$permission::forRole($role->value)) {
                            continue;
                        }

                        $syncPermissions[] = $permissionModel->id;
                    }
                }

                $roleModel->permissions()
                    ->sync($syncPermissions);
            }
        }
    }
}
