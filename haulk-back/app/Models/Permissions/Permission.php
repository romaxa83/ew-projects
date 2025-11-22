<?php

namespace App\Models\Permissions;

/**
 * @property int id
 * @property string name
 * @property string guard_name
 */
class Permission extends \Spatie\Permission\Models\Permission
{
    public const TABLE = 'permissions';
}
