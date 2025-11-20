<?php

namespace App\Models\JD;

use App\ModelFilters\JD\ManufactureFilter;
use App\Models\BaseModel;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 * @property int $id
 * @property int $jd_id
 * @property string $name
 * @property bool $status
 * @property int $is_partner_jd
 * @property int $position
 * @property string $created_at
 * @property string $updated_at
 */

class Manufacturer extends BaseModel
{
    use Filterable;

    const PARTNER_JD     = 1;
    const NOT_PARTNER_JD = 2;

    const TABLE = 'jd_manufacturers';
    protected $table = self::TABLE;

    protected $fillable = ['status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(ManufactureFilter::class);
    }

    public function isPartner()
    {
        return $this->is_partner_jd === self::PARTNER_JD;
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }
}
