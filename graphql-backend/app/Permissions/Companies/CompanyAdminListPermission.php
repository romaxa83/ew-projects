<?php

namespace App\Permissions\Companies;

use Core\Permissions\BasePermission;

class CompanyAdminListPermission extends BasePermission
{
    public const KEY = 'company.admin.list';

    public function getName(): string
    {
        return __('permissions.company.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
