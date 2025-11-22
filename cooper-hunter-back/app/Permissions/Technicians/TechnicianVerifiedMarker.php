<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianVerifiedMarker extends BasePermission
{
    public const KEY = 'technician.verify-marker';

    public function getName(): string
    {
        return __('permissions.technician.grants.verify-marker');
    }

    public function getPosition(): int
    {
        return 10000;
    }
}