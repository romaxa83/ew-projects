<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianCreatePermission extends BasePermission
{
    public const KEY = 'technician.create';

    public function getName(): string
    {
        return __('permissions.technician.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
