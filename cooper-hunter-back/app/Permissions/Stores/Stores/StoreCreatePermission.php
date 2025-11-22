<?php

namespace App\Permissions\Stores\Stores;

use Core\Permissions\BasePermission;

class StoreCreatePermission extends BasePermission
{
    public const KEY = StorePermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.store.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
