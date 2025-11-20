<?php

namespace App\Models\JD;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $jd_id
 * @property string $name
 * @property boolean $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class SizeParameters extends BaseModel
{
    const TABLE = 'jd_size_parameters';
    protected $table = self::TABLE;

    protected $fillable = [
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }
}
