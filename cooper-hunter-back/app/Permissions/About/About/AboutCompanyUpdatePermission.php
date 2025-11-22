<?php

namespace App\Permissions\About\About;

use Core\Permissions\BasePermission;

class AboutCompanyUpdatePermission extends BasePermission
{
    public const KEY = 'about_company.update';

    public function getName(): string
    {
        return __('permissions.about_company.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
