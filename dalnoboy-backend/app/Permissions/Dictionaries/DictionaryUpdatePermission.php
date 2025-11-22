<?php

namespace App\Permissions\Dictionaries;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class DictionaryUpdatePermission extends BasePermission
{
    public const KEY = DictionaryPermissionsGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.' . DictionaryPermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
