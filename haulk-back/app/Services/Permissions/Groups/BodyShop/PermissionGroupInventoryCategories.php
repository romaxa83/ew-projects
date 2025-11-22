<?php

namespace App\Services\Permissions\Groups\BodyShop;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupInventoryCategories extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'inventory-categories';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
        ];
    }
}
