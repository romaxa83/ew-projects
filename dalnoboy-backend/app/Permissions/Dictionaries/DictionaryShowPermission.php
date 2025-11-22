<?php

namespace App\Permissions\Dictionaries;

use Core\Permissions\BasePermission;

class DictionaryShowPermission extends BasePermission
{
    public const KEY = DictionaryPermissionsGroup::KEY . '.show';

    public function getName(): string
    {
        return __('permissions.' . DictionaryPermissionsGroup::KEY . '.grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return true;
    }
}
