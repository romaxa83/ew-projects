<?php

namespace App\Foundations\Modules\Permission\Models;

use Carbon\Carbon;

/**
 * @property int id
 * @property string name
 * @property string guard_name
 * @property string group
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */

class Permission extends \Spatie\Permission\Models\Permission
{
    public const TABLE = 'permissions';
}

