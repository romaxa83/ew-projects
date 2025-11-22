<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Users\User;

class EmployeeRoleDeleteMutation extends BaseRoleDeleteMutation
{
    public const NAME = 'employeeRoleDelete';

    protected function roleGuard(): string
    {
        return User::GUARD;
    }
}
