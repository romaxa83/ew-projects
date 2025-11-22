<?php

namespace App\Permissions\Fcm;

use Core\Permissions\BasePermission;

class FcmAddPermission extends BasePermission
{

    public const KEY = 'fcm.add';

    public function getName(): string
    {
        return __('permissions.fcm.grants.add');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
