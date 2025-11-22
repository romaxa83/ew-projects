<?php

namespace App\Permissions\SupportRequests;

use Core\Permissions\BasePermission;

class SupportRequestCreatePermission extends BasePermission
{
    public const KEY = 'support_request.create';

    public function getName(): string
    {
        return __('permissions.support_request.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
