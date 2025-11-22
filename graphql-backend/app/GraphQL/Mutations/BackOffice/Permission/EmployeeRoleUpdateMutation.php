<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Users\User;

class EmployeeRoleUpdateMutation extends BaseRoleUpdateMutation
{
    public const NAME = 'employeeRoleUpdate';

    protected function roleGuard(): string
    {
        return User::GUARD;
    }
}
