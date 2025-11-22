<?php

namespace App\Permissions\About\About;

use Core\Permissions\BasePermissionGroup;

class AboutCompanyPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'about_company';

    public function getName(): string
    {
        return __('permissions.about_company.group');
    }

    public function getPosition(): int
    {
        return 75;
    }
}
