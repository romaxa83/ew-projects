<?php

namespace App\Permissions\Catalog\Manuals;

use Core\Permissions\BasePermission;

class ManualCreatePermission extends BasePermission
{
    public const KEY = ManualPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.catalog.manual.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
