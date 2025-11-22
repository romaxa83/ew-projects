<?php

namespace App\Permissions\Saas\Companies;

use App\Permissions\BasePermissionGroup;

class CompanyPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'company';

    public function getName(): string
    {
        return __('permissions.company.group');
    }
}
