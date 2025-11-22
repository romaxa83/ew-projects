<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianToggleStatusPermission extends BasePermission
{
    public const KEY = 'technician.toggle_status';

    public function getName(): string
    {
        return __('permissions.technician.grants.toggle_status');
    }

    public function getPosition(): int
    {
        return 7;
    }
}
