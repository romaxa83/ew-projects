<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Users\User;

class UserRoleUpdateMutation extends BaseRoleUpdateMutation
{
    public const NAME = 'userRoleUpdate';

    protected function roleGuard(): string
    {
        return User::GUARD;
    }
}
