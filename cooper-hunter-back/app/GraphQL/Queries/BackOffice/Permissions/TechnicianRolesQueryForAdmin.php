<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Technicians\Technician;

class TechnicianRolesQueryForAdmin extends BaseRolesQuery
{
    public const NAME = 'technicianRolesForAdmin';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return Technician::GUARD;
    }
}
