<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianListPermission extends BasePermission
{
    public const KEY = 'technician.list';

    public function getName(): string
    {
        return __('permissions.technician.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
