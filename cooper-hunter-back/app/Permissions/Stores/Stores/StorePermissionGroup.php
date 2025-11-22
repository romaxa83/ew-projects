<?php

namespace App\Permissions\Stores\Stores;

use Core\Permissions\BasePermissionGroup;

class StorePermissionGroup extends BasePermissionGroup
{
    public const KEY = 'store';

    public function getName(): string
    {
        return __('permissions.store.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
