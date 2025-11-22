<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Users\User;

class UserRolesQueryForAdmin extends BaseRolesQuery
{
    public const NAME = 'userRolesForAdmin';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return User::GUARD;
    }
}
