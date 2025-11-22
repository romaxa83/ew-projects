<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupCompanyReports extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'company-reports';
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
