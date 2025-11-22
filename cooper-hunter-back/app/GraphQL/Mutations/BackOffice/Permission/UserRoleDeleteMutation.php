<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Users\User;

class UserRoleDeleteMutation extends BaseRoleDeleteMutation
{
    public const NAME = 'userRoleDelete';

    protected function roleGuard(): string
    {
        return User::GUARD;
    }
}
