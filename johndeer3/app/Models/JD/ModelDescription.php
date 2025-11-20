<?php

namespace App\Models\JD;

use App\ModelFilters\JD\ModelDescriptionFilter;
use App\Models\BaseModel;
use App\Models\Report\ReportMachine;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $jd_id
 * @property string $name
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property int $eg_jd_id
 *
 * @property-read EquipmentGroup equipmentGroup
 * @property-read Product product
 * @property-read Collection|ReportMachine[] reportMachine
 */

class ModelDescription extends BaseModel
{
    use Filterable;

    const TABLE = 'jd_model_descriptions';
    protected $table = self::TABLE;

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $fillable = [
        'status'
    ];

    public function modelFilter()
    {
        return $this->provideFilter(ModelDescriptionFilter::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function equipmentGroup(): BelongsTo
    {
        return $this->belongsTo(EquipmentGroup::class, 'eg_jd_id', 'jd_id');
//        return $this->belongsTo(EquipmentGroup::class, 'jd_id', 'eg_jd_id');
    }

    public function reportMachine(): HasMany
    {
        return $this->hasMany(ReportMachine::class);
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'jd_model_description_id', 'jd_id');
    }
}
