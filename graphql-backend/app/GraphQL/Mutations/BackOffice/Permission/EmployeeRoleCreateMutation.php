<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Users\User;

class EmployeeRoleCreateMutation extends BaseRoleCreateMutation
{
    public const NAME = 'employeeRoleCreate';

    protected function roleGuard(): string
    {
        return User::GUARD;
    }
}
