<?php

namespace App\Models\JD;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $jd_id
 * @property string $name
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 */

class Region extends BaseModel
{
    const TABLE = 'jd_regions';
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
