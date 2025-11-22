<?php

namespace App\Permissions\Catalog\Manuals;

use Core\Permissions\BasePermission;

class ManualDeletePermission extends BasePermission
{
    public const KEY = ManualPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.catalog.manual.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
