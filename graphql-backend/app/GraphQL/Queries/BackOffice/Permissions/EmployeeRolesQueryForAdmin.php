<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Users\User;

class EmployeeRolesQueryForAdmin extends BaseRolesQuery
{
    public const NAME = 'employeeRolesForAdmin';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return User::GUARD;
    }
}
