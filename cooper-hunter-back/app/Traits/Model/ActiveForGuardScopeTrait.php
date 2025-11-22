<?php

namespace App\Traits\Model;

use App\Contracts\Roles\HasGuardUser;
use App\Models\Technicians\Technician;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property bool active
 *
 * @see ActiveForGuardScopeTrait::scopeForGuard()
 * @method static Builder|static forGuard(HasGuardUser $auth)
 */
trait ActiveForGuardScopeTrait
{

    public function scopeForGuard(Builder|self $build, HasGuardUser $auth): void
    {
        if ($auth instanceof Technician) {
            $build->where('active', true);
        }
    }
}
