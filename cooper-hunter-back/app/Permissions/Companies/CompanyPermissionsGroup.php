<?php

namespace App\Permissions\Companies;

use Core\Permissions\BasePermissionGroup;

class CompanyPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'company';

    public function getName(): string
    {
        return __('permissions.company.group');
    }
}

