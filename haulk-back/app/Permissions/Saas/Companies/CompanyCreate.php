<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermission;

class CompanyCreate extends BasePermission
{
    public const KEY = 'company.create';

    public function getName(): string
    {
        return __('permissions.company.grants.create');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
