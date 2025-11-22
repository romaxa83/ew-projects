<?php

namespace App\Models\Permissions;

use App\Models\BaseModel;

class RoleHasPermission extends BaseModel
{
    public const TABLE = 'role_has_permissions';

    protected $table = self::TABLE;
}
