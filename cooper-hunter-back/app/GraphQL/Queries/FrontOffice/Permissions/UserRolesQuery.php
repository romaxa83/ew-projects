<?php

namespace App\GraphQL\Queries\FrontOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Users\User;

class UserRolesQuery extends BaseRolesQuery
{
    public const NAME = 'userRoles';

    public function __construct()
    {
        $this->setUserGuard();
    }

    protected function getRoleGuard(): string
    {
        return User::GUARD;
    }
}
