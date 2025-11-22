<?php

namespace App\Permissions\Companies;

use Core\Permissions\BasePermission;

class CompanyUpdatePermission extends BasePermission
{
    public const KEY = 'company.update';

    public function getName(): string
    {
        return __('permissions.company.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}


