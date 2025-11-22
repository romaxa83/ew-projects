<?php

namespace App\Traits\Permissions;

use App\Models\Permissions\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * @see \Spatie\Permission\Traits\HasRoles::roles()
 * @property Role[]|Collection $roles
 */
trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;
}
