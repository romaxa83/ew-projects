<?php


namespace App\Services\Permissions;


use Illuminate\Support\Collection;

interface PermissionContract
{
    public function getPermissions(array $permissions);

    public function getUserPermissions($user);

    public function getPermissionsGrid();

    public function getPermissionsProfile(Collection $permissionsCollection);

    public function getAllPermissions();
}
