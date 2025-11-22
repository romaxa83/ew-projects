<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\Models\Users\User;

class AvailableUserGrantsQuery extends BaseAvailablePermissionsQuery
{
    public const NAME = 'userAvailableGrants';

    protected function getPermissionGuard(): string
    {
        return User::GUARD;
    }
}
