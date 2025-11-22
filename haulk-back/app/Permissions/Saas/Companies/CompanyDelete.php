<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyDelete extends BasePermission
{
    public const KEY = 'company.delete';

    public function getName(): string
    {
        return __('permissions.company.grants.delete');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
