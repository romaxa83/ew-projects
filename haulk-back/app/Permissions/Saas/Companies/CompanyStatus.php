<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyStatus extends BasePermission
{
    public const KEY = 'company.status';

    public function getName(): string
    {
        return __('permissions.company.grants.status');
    }

    public function getPosition(): int
    {
        return 25;
    }
}
