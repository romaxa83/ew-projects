<?php

namespace App\Permissions\Members;

use Core\Permissions\BasePermission;

class MemberVerifyEmailPermission extends BasePermission
{
    public const KEY = 'member.verify_email';

    public function getName(): string
    {
        return __('permissions.member.verify_email');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
