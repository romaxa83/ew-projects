<?php

namespace App\Repositories\Permission;

use App\Models\Permission\Role;
use App\Repositories\AbstractRepository;

class RoleRepository extends AbstractRepository
{
    public function query()
    {
        return Role::query();
    }
}
