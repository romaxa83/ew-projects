<?php


namespace App\Permissions\Saas\Support;


use App\Permissions\BasePermission;

class SupportRequestUpdate extends BasePermission
{
    public const KEY = 'support-requests.update';

    public function getName(): string
    {
        return __('permissions.support-requests.grants.update');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
