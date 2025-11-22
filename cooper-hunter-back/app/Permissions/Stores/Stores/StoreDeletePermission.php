<?php

namespace App\Permissions\Stores\Stores;

use Core\Permissions\BasePermission;

class StoreDeletePermission extends BasePermission
{
    public const KEY = StorePermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.store.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
