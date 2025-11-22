<?php

namespace App\Permissions\Companies;

use Core\Permissions\BasePermission;

class CompanyListPermission extends BasePermission
{
    public const KEY = 'company.list';

    public function getName(): string
    {
        return __('permissions.company.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
