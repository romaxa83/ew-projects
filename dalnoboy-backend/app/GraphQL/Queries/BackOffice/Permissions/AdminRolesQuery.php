<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\Enums\Permissions\GuardsEnum;
use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;

class AdminRolesQuery extends BaseRolesQuery
{
    public const NAME = 'adminRoles';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return GuardsEnum::ADMIN;
    }
}
