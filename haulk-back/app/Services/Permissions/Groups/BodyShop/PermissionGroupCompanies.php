<?php

namespace App\Services\Permissions\Groups\BodyShop;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupCompanies extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'companies-bs';
    }

    public function getPermissions(): array
    {
        return [];
    }
}
