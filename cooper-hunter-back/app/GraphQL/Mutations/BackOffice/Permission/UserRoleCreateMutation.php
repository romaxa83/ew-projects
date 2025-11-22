<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Users\User;

class UserRoleCreateMutation extends BaseRoleCreateMutation
{
    public const NAME = 'userRoleCreate';

    protected function roleGuard(): string
    {
        return User::GUARD;
    }
}
