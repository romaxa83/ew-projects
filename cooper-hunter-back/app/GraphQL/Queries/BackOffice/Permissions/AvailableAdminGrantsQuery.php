<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\Models\Admins\Admin;

class AvailableAdminGrantsQuery extends BaseAvailablePermissionsQuery
{
    public const NAME = 'adminAvailableGrants';

    protected function getPermissionGuard(): string
    {
        return Admin::GUARD;
    }
}
