<?php

namespace App\Permissions\Saas\GPS\History;

use App\Permissions\BasePermissionGroup;

class HistoryPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'gps-history';

    public function getName(): string
    {
        return __('permissions.gps-history.group');
    }
}


