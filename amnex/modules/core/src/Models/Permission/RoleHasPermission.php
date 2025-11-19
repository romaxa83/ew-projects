<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Permission;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $role_id
 * @property int $permission_id
 */
class RoleHasPermission extends Pivot
{
    public $timestamps = false;
    protected $table = 'role_has_permissions';
}
