<?php


namespace App\Permissions\Saas\Support;


use App\Permissions\BasePermission;

class SupportRequestShow extends BasePermission
{
    public const KEY = 'support-requests.show';

    public function getName(): string
    {
        return __('permissions.support-requests.grants.show');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
