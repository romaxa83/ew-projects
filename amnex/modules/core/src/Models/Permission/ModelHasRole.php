<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Permission;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $role_id
 * @property string $model_type
 * @property int $model_id
 * @property-read Role $role
 *
 * @see ModelHasRole::role()
 */
class ModelHasRole extends Pivot
{
    public $timestamps = false;
    protected $table = 'model_has_roles';

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
