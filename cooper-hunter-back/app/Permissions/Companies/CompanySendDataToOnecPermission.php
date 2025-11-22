<?php

namespace App\Permissions\Companies;

use Core\Permissions\BasePermission;

class CompanySendDataToOnecPermission extends BasePermission
{
    public const KEY = 'company.send_data';

    public function getName(): string
    {
        return __('permissions.company.grants.send_data');
    }

    public function getPosition(): int
    {
        return 5;
    }
}
