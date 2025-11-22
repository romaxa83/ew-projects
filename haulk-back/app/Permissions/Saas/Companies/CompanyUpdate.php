<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyUpdate extends BasePermission
{
    public const KEY = 'company.update';

    public function getName(): string
    {
        return __('permissions.admin.grants.update');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
