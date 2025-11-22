<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianDeletePermission extends BasePermission
{
    public const KEY = 'technician.delete';

    public function getName(): string
    {
        return __('permissions.technician.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
