<?php

namespace App\Models\Catalogs\Car;

use App\Casts\UuidCast;
use App\Models\BaseModel;
use App\Models\Catalogs\Calc\CalcModel;
use App\Models\Media\Image;
use App\Traits\Media\ImageRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $uuid
 * @property bool $active
 * @property int $sort
 * @property string $name
 * @property string $brand_id
 * @property bool $for_credit
 * @property bool $for_calc
 *
 */
class Model extends BaseModel
{
    use ImageRelation;

    public $timestamps = false;

    public const TABLE = 'car_models';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool',
        'for_credit' => 'bool',
        'for_calc' => 'bool',
        'uuid' => UuidCast::class,
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function calcs(): HasMany
    {
        return $this->hasMany(CalcModel::class, 'model_id', 'id');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'entity');
    }

    public function scopeModelNameSearch(Builder $query, string $search)
    {
        return $query->where('name','like', $search . '%');
    }

    public function scopeBrandNameSearch(Builder $query, string $search)
    {
        return $query
            ->with('brand')
            ->whereHas('brand', function ($q) use ($search){
                $q->where('name','like', $search . '%');
            });
    }

    public function scopeBrandSort(Builder $query, string $val)
    {
        if($this->checkGraphqlSort($val)){
            return $query
                ->select(['car_brands.*', 'car_models.*'])
                ->join('car_brands', 'car_brands.id' , '=', 'car_models.brand_id')
                ->orderBy('car_brands.name', $val);
        }
    }
}
