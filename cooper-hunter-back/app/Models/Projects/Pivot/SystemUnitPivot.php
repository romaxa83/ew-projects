<?php

namespace App\Models\Projects\Pivot;

use App\Models\BasePivot;
use App\Models\Projects\System;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int system_id
 * @property int product_id
 * @property string serial_number
 */
class SystemUnitPivot extends BasePivot
{
    public const TABLE = 'system_unit';

    protected $table = self::TABLE;

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
}
