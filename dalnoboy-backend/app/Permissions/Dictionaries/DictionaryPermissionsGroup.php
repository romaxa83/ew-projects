<?php

namespace App\Permissions\Dictionaries;

use Core\Permissions\BasePermissionGroup;

class DictionaryPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'dictionary';

    public function getName(): string
    {
        return __('permissions.dictionary.group');
    }

    public function getPosition(): int
    {
        return 0;
    }
}
