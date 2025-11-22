<?php

namespace Tests\Builders\Roles;

use App\Foundations\Modules\Permission\Models\Role;
use Tests\Builders\BaseBuilder;

class RoleBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Role::class;
    }
}

