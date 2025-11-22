<?php


namespace App\Permissions\Saas\Support;


use App\Permissions\BasePermission;

class SupportRequestChangeManager extends BasePermission
{
    public const KEY = 'support-requests.change-manager';

    public function getName(): string
    {
        return __('permissions.support-requests.grants.change-manager');
    }

    public function getPosition(): int
    {
        return 50;
    }
}

