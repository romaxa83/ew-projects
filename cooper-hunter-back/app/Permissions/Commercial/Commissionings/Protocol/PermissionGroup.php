<?php

namespace App\Permissions\Commercial\Commissionings\Protocol;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commissioning_protocol';

    public function getName(): string
    {
        return __('permissions.commissioning.protocol.group');
    }
}

