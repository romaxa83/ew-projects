<?php


namespace App\Permissions\Saas\Support;


use App\Permissions\BasePermissionGroup;

class SupportRequestPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'support-requests';

    public function getName(): string
    {
        return __('permissions.support-requests.group');
    }
}
