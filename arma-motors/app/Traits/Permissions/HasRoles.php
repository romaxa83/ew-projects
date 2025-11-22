<?php

namespace App\Traits\Permissions;

use App\Models\Permission\Role;
use Illuminate\Database\Eloquent\Collection;


/**
 * @see HasRoles::getRoleAttribute()
 * @property-read null|Role $role
 * @property-read Collection|Role[] $roles
 */
trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }

    public function deleteExistRoles(): void
    {
        $this->roles()->detach();
    }
}

