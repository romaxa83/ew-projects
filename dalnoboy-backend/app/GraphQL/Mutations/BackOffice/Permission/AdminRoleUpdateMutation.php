<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Admins\Admin;

class AdminRoleUpdateMutation extends BaseRoleUpdateMutation
{
    public const NAME = 'updateAdminRole';

    protected function roleGuard(): string
    {
        return Admin::GUARD;
    }
}
