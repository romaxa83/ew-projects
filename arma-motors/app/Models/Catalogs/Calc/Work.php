<?php

namespace App\Models\Catalogs\Calc;

use App\Helpers\ConvertNumber;
use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use App\Services\Calc\CompositeItemCalcInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 *
 */
class Work extends BaseModel implements CompositeItemCalcInterface
{
    use HasFactory;

    public $timestamps = false;

    public const DEFAULT_MINUTES = 0;

    public const TABLE = 'works';
    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    protected $appends = [
        'current'
    ];

    // relations

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(
            Brand::class,
            'car_brand_work_relations',
            'work_id', 'brand_id'
        );
    }
    public function translations(): HasMany
    {
        return $this->hasMany(WorkTranslation::class, 'model_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(WorkTranslation::class,'model_id', 'id')->where('lang', \App::getLocale());
    }

    // scopes

    public function scopeByBrand(EloquentBuilder $query, $brandId): EloquentBuilder
    {
        return $query->with('brands')
            ->whereHas('brands', function(EloquentBuilder $q) use ($brandId) {
                $q->where('id', $brandId);
            });
    }

    // методы интерфейса для калькуляции ТО

    public function calcPrice(null|float $price = null): float
    {
        return $price * $this->qty();
    }

    public function calcPriceDiscount(null|float $price = null): null|float
    {
        return null != $price ? $price * $this->qty(): null;
    }

    public function name(): string
    {
        return $this->current->name;
    }

    public function qty(): string
    {
        return ConvertNumber::fromNumberToFloat($this->pivot->minutes);
    }

    public function unit(): string
    {
        return __("translation.work.unit");
    }
}
