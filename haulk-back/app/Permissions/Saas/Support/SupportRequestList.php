<?php


namespace App\Permissions\Saas\Support;


use App\Permissions\BasePermission;

class SupportRequestList extends BasePermission
{
    public const KEY = 'support-requests.list';

    public function getName(): string
    {
        return __('permissions.support-requests.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}

