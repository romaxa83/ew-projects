<?php

declare(strict_types=1);

namespace App\Foundations\Modules\Permission\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int role_id
 * @property int permission_id
 */
class RoleHasPermission extends Pivot
{
    public const TABLE = 'role_has_permissions';

    public $timestamps = false;

    protected $table = self::TABLE;
}

