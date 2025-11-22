<?php

namespace App\GraphQL\Queries\FrontOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Users\User;

class EmployeeRolesQueryForCompany extends BaseRolesQuery
{
    public const NAME = 'employeeRolesForCompany';

    protected function getRoleGuard(): string
    {
        return User::GUARD;
    }
}
