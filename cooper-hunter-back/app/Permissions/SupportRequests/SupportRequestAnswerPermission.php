<?php

namespace App\Permissions\SupportRequests;

use Core\Permissions\BasePermission;

class SupportRequestAnswerPermission extends BasePermission
{
    public const KEY = 'support_request.answer';

    public function getName(): string
    {
        return __('permissions.support_request.grants.answer');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
