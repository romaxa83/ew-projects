<?php

namespace App\Permissions\Technicians;

use Core\Permissions\BasePermission;

class TechnicianArchiveListPermission extends BasePermission
{
    public const KEY = 'technician.list-archive';

    public function getName(): string
    {
        return __('permissions.technician.grants.list-archive');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
