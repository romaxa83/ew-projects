<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static Builder|static query()
 *
 * @mixin BaseModel
 */
class BasePivot extends Pivot
{

}
