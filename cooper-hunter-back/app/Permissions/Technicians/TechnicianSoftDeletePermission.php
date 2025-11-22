<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianSoftDeletePermission extends BasePermission
{
    public const KEY = 'technician.delete.soft';

    public function getName(): string
    {
        return __('permissions.technician.grants.delete-soft');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
