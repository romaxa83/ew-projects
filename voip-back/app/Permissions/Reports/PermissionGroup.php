<?php

namespace App\Permissions\Reports;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'reports';

    public function getName(): string
    {
        return __('permissions.reports.group');
    }
}

