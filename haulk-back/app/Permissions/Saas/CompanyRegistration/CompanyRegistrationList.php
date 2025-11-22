<?php


namespace App\Permissions\Saas\CompanyRegistration;


use App\Permissions\BasePermission;

class CompanyRegistrationList extends BasePermission
{
    public const KEY = 'company-registration.list';

    public function getName(): string
    {
        return __('permissions.company-registration.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
