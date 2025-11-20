<?php

namespace Tests\Builders\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use Tests\Builders\BaseBuilder;

class RoleBuilder extends BaseBuilder
{
    protected string $guard = Admin::GUARD;

    function modelClass(): string
    {
        return Role::class;
    }

    protected function beforeSave(): void
    {
        $this->data = array_merge($this->data, [
            'guard_name' => $this->guard
        ]);
    }
}
