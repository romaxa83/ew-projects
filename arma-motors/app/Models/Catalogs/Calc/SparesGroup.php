<?php

namespace App\Models\Catalogs\Calc;

use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property int $type
 * @property int $brand_id
 *
 */
class SparesGroup extends BaseModel
{
    public $timestamps = false;

    public const TYPE_QTY    = 1;   // измерение в кол-ве (шайба, свеча ...)
    public const TYPE_VOLUME = 2;   // измерение в обьеме (масло ...)

    public const TABLE = 'spares_groups';
    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function getUnitName(): string
    {
        if($this->type === self::TYPE_QTY){
            return __("translation.spares.qty");
        }
        if($this->type === self::TYPE_VOLUME){
            return __("translation.spares.volume");
        }
        return '';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SparesGroupTranslation::class, 'group_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(SparesGroupTranslation::class,'group_id', 'id')->where('lang', \App::getLocale());
    }

    public function spares(): HasMany
    {
        return $this->hasMany(Spares::class, 'group_id', 'id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
