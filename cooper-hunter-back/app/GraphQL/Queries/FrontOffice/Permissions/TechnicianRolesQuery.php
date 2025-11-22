<?php

namespace App\GraphQL\Queries\FrontOffice\Permissions;

use App\GraphQL\Queries\Common\Permissions\BaseRolesQuery;
use App\Models\Technicians\Technician;

class TechnicianRolesQuery extends BaseRolesQuery
{
    public const NAME = 'technicianRoles';

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    protected function getRoleGuard(): string
    {
        return Technician::GUARD;
    }
}
