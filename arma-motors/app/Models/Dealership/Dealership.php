<?php

namespace App\Models\Dealership;

use App\Models\AA\AAPost;
use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use App\Models\Media\Image;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point|null $location
 * @property bool $active
 * @property int $sort
 * @property string $brand_id
 * @property string $website
 * @property string|null $alias
 * @property string created_at
 * @property string updated_at
 *
 * @property-read AAPost[]|Collection post
 *
 */
class Dealership extends BaseModel
{
    use SpatialTrait;

    public const TABLE = 'dealerships';

    public const ARMO_MOTORS_RENAULT = 'arma-motors-renault';
    public const VIKING_MOTORS = 'viking-motors';
    public const ARMO_MOTORS_MITSUBISHI = 'arma-motors-mitsubishi';

    protected $table = self::TABLE;

    protected $spatialFields = [
        'location',
    ];

    protected $casts = [
        'active' => 'bool'
    ];

    // Relations
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'entity');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DealershipTranslation::class, 'dealership_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(DealershipTranslation::class,'dealership_id', 'id')->where('lang', \App::getLocale());
    }
    // Departments
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function department(): HasOne
    {
        return $this->hasOne(Department::class);
    }

    public function departmentSales(): HasOne
    {
        return $this->department()->where('type', Department::TYPE_SALES);
    }

    public function departmentService(): HasOne
    {
        return $this->department()->where('type', Department::TYPE_SERVICE);
    }

    public function departmentCash(): HasOne
    {
        return $this->department()->where('type', Department::TYPE_CREDIT);
    }

    public function departmentBody(): HasOne
    {
        return $this->department()->where('type', Department::TYPE_BODY);
    }

    public function timeStep(): HasMany
    {
        return $this->hasMany(TimeStep::class, 'dealership_id', 'id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(AAPost::class, 'alias', 'alias');
    }
}
