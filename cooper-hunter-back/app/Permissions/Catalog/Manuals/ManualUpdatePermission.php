<?php

namespace App\Permissions\Catalog\Manuals;

use Core\Permissions\BasePermission;

class ManualUpdatePermission extends BasePermission
{
    public const KEY = ManualPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.catalog.manual.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
