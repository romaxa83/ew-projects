<?php

namespace App\Permissions\Catalog\Labels;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.label.create';

    public function getName(): string
    {
        return __('permissions.catalog.label.grants.create');
    }

    public function getPosition(): int
    {
        return 41;
    }
}

