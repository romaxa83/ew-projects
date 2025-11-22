<?php

namespace App\Permissions\Stores\Stores;

use Core\Permissions\BasePermission;

class StoreUpdatePermission extends BasePermission
{
    public const KEY = StorePermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.store.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
