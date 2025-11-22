<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\Enums\Permissions\GuardsEnum;
use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;

class UserRolesQuery extends BaseRolesQuery
{
    public const NAME = 'userRoles';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return GuardsEnum::USER;
    }
}
