<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Models\Admins\Admin;

class AdminRoleDeleteMutation extends BaseRoleDeleteMutation
{
    public const NAME = 'AdminRoleDelete';

    protected function roleGuard(): string
    {
        return Admin::GUARD;
    }
}
