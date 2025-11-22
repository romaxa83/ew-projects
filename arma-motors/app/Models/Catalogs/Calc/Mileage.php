<?php

namespace App\Models\Catalogs\Calc;

use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property bool $active
 * @property int $value
 *
 */
class Mileage extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'mileages';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    // relations

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(
            Brand::class,
            'car_brand_mileage_relations',
            'mileage_id', 'brand_id'
        );
    }

    // scopes

    public function scopeByBrand(EloquentBuilder $query, $brandId): EloquentBuilder
    {
        return $query->with('brands')
            ->whereHas('brands', function(EloquentBuilder $q) use ($brandId) {
                $q->where('id', $brandId);
            });
    }
}
