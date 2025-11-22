<?php

namespace App\Permissions\Catalog\Manuals;

use Core\Permissions\BasePermission;

class ManualListPermission extends BasePermission
{
    public const KEY = ManualPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.catalog.manual.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
