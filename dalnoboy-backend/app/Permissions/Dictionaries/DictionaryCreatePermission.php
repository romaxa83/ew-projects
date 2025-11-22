<?php

namespace App\Permissions\Dictionaries;

use Core\Permissions\BasePermission;

class DictionaryCreatePermission extends BasePermission
{
    public const KEY = DictionaryPermissionsGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.' . DictionaryPermissionsGroup::KEY . '.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }

    public static function forRole(string $role): bool
    {
        return true;
    }
}
