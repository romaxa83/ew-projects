<?php

namespace App\Permissions\Catalog\Labels;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.label.delete';

    public function getName(): string
    {
        return __('permissions.catalog.label.grants.delete');
    }

    public function getPosition(): int
    {
        return 43;
    }
}
