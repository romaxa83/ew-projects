<?php


namespace App\Services\Permissions\Templates;


abstract class AbstractRole
{
    /**
     * @var string
     */
    protected $roleName;
    /**
     * @var array
     */
    protected $permissions = [];

    /**
     *  Set permission to user
     */
    public function setPermissions()
    {
        $syncPermissions = [];
        foreach ($this->permissions as $moduleName => $permissions) {
            foreach ($permissions as $permission) {
                $syncPermissions[] = $moduleName . ' ' . $permission;
            }
        }
        return $syncPermissions;
    }

    public function getPermission()
    {
        return $this->permissions;
    }
}
