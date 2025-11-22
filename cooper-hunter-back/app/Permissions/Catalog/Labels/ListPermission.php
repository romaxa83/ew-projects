<?php

namespace App\Permissions\Catalog\Labels;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.label.list';

    public function getName(): string
    {
        return __('permissions.catalog.label.grants.list');
    }

    public function getPosition(): int
    {
        return 44;
    }
}
