<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\Models\Employees\Employee;

class AvailableEmployeeGrantsQuery extends BaseAvailablePermissionsQuery
{
    public const NAME = 'AvailableEmployeeGrants';

    protected function getPermissionGuard(): string
    {
        return Employee::GUARD;
    }
}

