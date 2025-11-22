<?php

namespace App\Permissions\Companies;

use Core\Permissions\BasePermission;

class CompanySendCodePermission extends BasePermission
{
    public const KEY = 'company.send_code';

    public function getName(): string
    {
        return __('permissions.company.grants.send_code');
    }

    public function getPosition(): int
    {
        return 4;
    }
}



