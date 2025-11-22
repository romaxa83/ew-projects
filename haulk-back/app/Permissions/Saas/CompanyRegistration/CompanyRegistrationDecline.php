<?php


namespace App\Permissions\Saas\CompanyRegistration;


use App\Permissions\BasePermission;

class CompanyRegistrationDecline extends BasePermission
{
    public const KEY = 'company-registration.decline';

    public function getName(): string
    {
        return __('permissions.company-registration.grants.decline');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
