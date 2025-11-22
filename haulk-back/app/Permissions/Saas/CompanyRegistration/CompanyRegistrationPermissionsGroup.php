<?php


namespace App\Permissions\Saas\CompanyRegistration;


use App\Permissions\BasePermissionGroup;

class CompanyRegistrationPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'company-registration';

    public function getName(): string
    {
        return __('permissions.company-registration.group');
    }
}
