<?php


namespace App\Permissions\Saas\CompanyRegistration;


use App\Permissions\BasePermission;

class CompanyRegistrationApprove extends BasePermission
{
    public const KEY = 'company-registration.approve';

    public function getName(): string
    {
        return __('permissions.company-registration.grants.approve');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
