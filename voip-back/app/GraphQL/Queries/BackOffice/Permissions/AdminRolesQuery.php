<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Admins\Admin;

class AdminRolesQuery extends BaseRolesQuery
{
    public const NAME = 'AdminRoles';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return Admin::GUARD;
    }
}
