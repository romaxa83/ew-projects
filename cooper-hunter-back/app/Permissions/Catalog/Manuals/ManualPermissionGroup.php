<?php

namespace App\Permissions\Catalog\Manuals;

use Core\Permissions\BasePermissionGroup;

class ManualPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.manual';

    public function getName(): string
    {
        return __('permissions.catalog.manual.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
