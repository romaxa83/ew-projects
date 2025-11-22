<?php

namespace App\Permissions\Stores\Stores;

use Core\Permissions\BasePermission;

class StoreListPermission extends BasePermission
{
    public const KEY = StorePermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.store.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
