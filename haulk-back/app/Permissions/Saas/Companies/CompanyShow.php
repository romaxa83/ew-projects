<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyShow extends BasePermission
{
    public const KEY = 'company.show';

    public function getName(): string
    {
        return __('permissions.admin.grants.show');
    }

    public function getPosition(): int
    {
        return 20;
    }
}
