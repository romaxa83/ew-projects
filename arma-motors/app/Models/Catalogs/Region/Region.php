<?php

namespace App\Models\Catalogs\Region;

use App\Traits\HasFactory;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property string created_at
 * @property string updated_at
 *
 */
class Region extends BaseModel
{
    use HasFactory;

    public const TABLE = 'regions';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    // relations
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
    public function translations(): HasMany
    {
        return $this->hasMany(RegionTranslation::class, 'region_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(RegionTranslation::class,'region_id', 'id')->where('lang', \App::getLocale());
    }
}
