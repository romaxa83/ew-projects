<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermissionGroup;

class TechnicianPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'technician';

    public function getName(): string
    {
        return __('permissions.technician.group');
    }
}
