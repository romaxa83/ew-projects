<?php

namespace App\Permissions\SupportRequests;

use Core\Permissions\BasePermission;

class SupportRequestListPermission extends BasePermission
{
    public const KEY = 'support_request.list';

    public function getName(): string
    {
        return __('permissions.support_request.grants.list');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
