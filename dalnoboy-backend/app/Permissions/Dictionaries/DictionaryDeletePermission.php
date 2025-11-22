<?php

namespace App\Permissions\Dictionaries;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class DictionaryDeletePermission extends BasePermission
{
    public const KEY = DictionaryPermissionsGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.' . DictionaryPermissionsGroup::KEY . '.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
