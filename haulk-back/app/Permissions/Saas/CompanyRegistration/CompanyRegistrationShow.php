<?php


namespace App\Permissions\Saas\CompanyRegistration;


use App\Permissions\BasePermission;

class CompanyRegistrationShow extends BasePermission
{
    public const KEY = 'company-registration.show';

    public function getName(): string
    {
        return __('permissions.company-registration.grants.show');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
