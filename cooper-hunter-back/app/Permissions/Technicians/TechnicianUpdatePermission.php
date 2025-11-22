<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianUpdatePermission extends BasePermission
{
    public const KEY = 'technician.update';

    public function getName(): string
    {
        return __('permissions.technician.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
