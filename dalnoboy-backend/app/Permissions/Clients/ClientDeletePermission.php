<?php

namespace App\Permissions\Clients;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class ClientDeletePermission extends BasePermission
{
    public const KEY = ClientPermissionsGroup::KEY . '.delete';

    public static function forRole(string $role): bool
    {
        return AdminRolesEnum::SUPER_ADMIN === $role;
    }

    public function getName(): string
    {
        return __('permissions.' . ClientPermissionsGroup::KEY . '.grants.delete');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
