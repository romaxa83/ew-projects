<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Admins\Admin;

class AdminRoleCreateMutation extends BaseRoleCreateMutation
{
    public const NAME = 'adminRoleCreate';

    protected function roleGuard(): string
    {
        return Admin::GUARD;
    }
}
