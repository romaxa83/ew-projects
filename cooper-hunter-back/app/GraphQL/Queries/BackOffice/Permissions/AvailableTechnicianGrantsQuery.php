<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\Models\Technicians\Technician;

class AvailableTechnicianGrantsQuery extends BaseAvailablePermissionsQuery
{
    public const NAME = 'technicianAvailableGrants';

    protected function getPermissionGuard(): string
    {
        return Technician::GUARD;
    }
}
