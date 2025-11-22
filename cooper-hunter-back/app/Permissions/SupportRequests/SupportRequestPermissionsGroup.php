<?php

namespace App\Permissions\SupportRequests;

use Core\Permissions\BasePermissionGroup;

class SupportRequestPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'support_request';

    public function getName(): string
    {
        return __('permissions.support_request.group');
    }

    public function getPosition(): int
    {
        return 100;
    }
}
