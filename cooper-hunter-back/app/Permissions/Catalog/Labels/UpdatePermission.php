<?php

namespace App\Permissions\Catalog\Labels;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.label.update';

    public function getName(): string
    {
        return __('permissions.catalog.label.grants.update');
    }

    public function getPosition(): int
    {
        return 42;
    }
}
