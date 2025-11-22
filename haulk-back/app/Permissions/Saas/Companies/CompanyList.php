<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyList extends BasePermission
{
    public const KEY = 'company.list';

    public function getName(): string
    {
        return __('permissions.company.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
