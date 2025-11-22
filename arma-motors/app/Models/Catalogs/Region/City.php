<?php

namespace App\Models\Catalogs\Region;

use App\Models\BaseModel;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point|null $location
 * @property bool $active
 * @property int $sort
 * @property int $region_id
 * @property string created_at
 * @property string updated_at
 *
 */
class City extends BaseModel
{
    use SpatialTrait;

    public const TABLE = 'cities';

    protected $table = self::TABLE;

    protected $spatialFields = [
        'location',
    ];

    protected $casts = [
        'active' => 'bool'
    ];

    // relations
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CityTranslation::class, 'city_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(CityTranslation::class,'city_id', 'id')->where('lang', \App::getLocale());
    }
}

