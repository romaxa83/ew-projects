<?php


namespace App\Services\Permissions;


use App\Models\Admins\Admin;
use App\Models\Users\User;
use Exception;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionWorker implements PermissionContract
{
    /**
     * @var User|null
     */
    private $user;
    /**
     * @var array
     */
    private $grid;

    /**
     * @param array|null $permissions
     * @return array
     */
    public function getPermissions(?array $permissions): array
    {
        $newPermissions = [];
        if (!empty($permissions)) {
            foreach ($permissions as $blockName => $permission) {
                if ($blockName === 'profile') {
                    continue; // TODO razobratsa s etim
                }
                if (!empty($permission)) {
                    foreach ($permission as $methodName => $value) {
                        if ($value) {
                            $permissionAsString = $blockName . ' ' . $methodName;
                            $newPermissions[] = $permissionAsString;
                        }
                    }
                }
            }
        }
        return $newPermissions;
    }

    /**
     * @param User $user
     * @return Collection
     * @throws Exception
     */
    public function getUserPermissions($user): Collection
    {
        $this->user = $user;
        return $this->user->getAllPermissions();
    }

    public function getAdminPermissions(Admin $admin): Collection
    {
        return $admin->getAllPermissions();
    }

    /**
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * @return array
     */
    public function getPermissionsGrid(): array
    {
        return $this->grid;
    }

    /**
     * @param Collection $permissionsCollection
     * @return array
     */
    public function getPermissionsProfile(Collection $permissionsCollection): array
    {
        $permissionsProfile = [];
        $permissionsCollection->each(
            function (Permission $permission) use (&$permissionsProfile) {
                $blockNameArr = explode(' ', $permission->name);
                if (count($blockNameArr) > 1) {
                    $permissionsProfile[$blockNameArr[0]][] = str_replace($blockNameArr[0] . ' ', '', $permission->name);
                }
            }
        );
        return $permissionsProfile;
    }

    public function getPermissionsAdmin(Collection $adminsPermissions): array
    {
        $result = [];

        $adminsPermissions->each(
            function (Permission $permission) use (&$result) {
                $arr = explode('.', $permission->name);

                if (count($arr) <= 1) {
                    return;
                }

                $group = $arr[0];
                unset($arr[0]);

                $result[$group][] = implode(' ', $arr);
            }
        );

        return $result;
    }

    /**
     * Set grid for front
     *
     * @param null $user
     * @param string $roleName
     * @throws Exception
     */
    public function setPermissionGrid($user = null, string $roleName = User::DISPATCHER_ROLE)
    {
        $allPermission = $this->setValuesToPermissions($this->getPermissionsProfile($this->getAllPermissions()));
        if ($user) {
            $permissions = $this->setValuesToPermissions(
                $this->getPermissionsProfile($this->getUserPermissions($user))
            );
        } else {
            $permissions = $this->setValuesToPermissions(
                (resolve("App\Services\Permissions\Items\\$roleName"))->getPermission()
            );
        }
        $differencePermission = array_diff_assoc_recursive($allPermission, $permissions);
        $notAllowedPermissions = [];
        foreach ($differencePermission as $blockName => $value) {
            foreach ($value as $methodName => $item) {
                $notAllowedPermissions[$blockName][$methodName] = false;
            }
        }
        $this->grid = array_merge_recursive_distinct($allPermission, $notAllowedPermissions);
    }

    /**
     * @param array $data
     * @return array
     */
    private function setValuesToPermissions(array $data): array
    {
        $permissions = [];
        foreach ($data as $key => $value) {
            if ($key === 'profile') {
                continue; // TODO razobratsa s etim
            }
            $permissions[$key] = array_fill_keys($value, true);
        }
        return $permissions;
    }

}
