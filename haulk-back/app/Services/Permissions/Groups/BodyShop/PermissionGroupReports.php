<?php

namespace App\Services\Permissions\Groups\BodyShop;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupReports extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'reports-bs';
    }

    public function getPermissions(): array
    {
        return [
            'orders',
            'inventories',
        ];
    }
}
