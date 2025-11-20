<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Admins\Admin;

class AdminRoleCreateMutation extends BaseRoleCreateMutation
{
    public const NAME = 'AdminRoleCreate';

    protected function roleGuard(): string
    {
        return Admin::GUARD;
    }
}
