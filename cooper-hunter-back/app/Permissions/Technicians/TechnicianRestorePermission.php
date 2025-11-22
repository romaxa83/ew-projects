<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianRestorePermission extends BasePermission
{
    public const KEY = 'technician.restore';

    public function getName(): string
    {
        return __('permissions.technician.grants.restore');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
