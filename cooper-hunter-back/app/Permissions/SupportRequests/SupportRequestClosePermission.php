<?php

namespace App\Permissions\SupportRequests;

use Core\Permissions\BasePermission;

class SupportRequestClosePermission extends BasePermission
{
    public const KEY = 'support_request.close';

    public function getName(): string
    {
        return __('permissions.support_request.grants.close');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
