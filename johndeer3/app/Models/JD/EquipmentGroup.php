<?php

namespace App\Models\JD;

use App\ModelFilters\JD\EquipmentGroupFilter;
use App\Models\BaseModel;
use App\Models\Report\Feature\Feature;
use App\Models\Report\Feature\FeatureEGPivot;
use App\Models\Report\Feature\FeatureSubEGPivot;
use App\Models\Report\ReportMachine;
use App\Models\User\User;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $jd_id
 * @property string $name
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property bool $for_statistic
 *
 * @property-read ModelDescription[]|Collection modelDescriptions
 * @property-read Feature[]|Collection features
 * @property-read Feature[]|Collection subFeatures
 * @property-read ReportMachine[]|Collection reportMachines
 * @property-read EquipmentGroup[]|Collection relatedEgs
 * @property-read User[]|Collection psss
 */

class EquipmentGroup extends BaseModel
{
    use Filterable;

    const TABLE = 'jd_equipment_groups';
    protected $table = self::TABLE;

    protected $fillable = [
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'for_statistic' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(EquipmentGroupFilter::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function modelDescriptions(): HasMany
    {
        return $this->hasMany(ModelDescription::class, 'eg_jd_id', 'jd_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, FeatureEGPivot::tableName(), 'eg_id', 'feature_id');
    }

    public function subFeatures(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, FeatureSubEGPivot::tableName(), 'eg_id', 'feature_id');
    }

    public function featuresActive($type = null)
    {
        $query = $this->features()->where('active', true);

        if($type){
            $query->where('type', $type);
        }

        return $query->orderBy('position')->get();
    }

    public function subFeaturesActive()
    {
        return $this->subFeatures()->where('active', true)->orderBy('position')->get();
    }

    public function relatedEgs(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'eq_group_relation', 'eg_id', 'sub_eg_id');
    }

    public function psss(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_eg_relation',
            'eg_id', 'user_id'
        );
    }

    public function reportMachines(): HasMany
    {
        return $this->hasMany(ReportMachine::class);
    }

    public static function forStatistics(): array
    {
        return [
            'Combines',
            'SPFHs',
            'Self-Prop. Sprayers',
            'Large Tractors (7, 8, 9 Series)',
            'Mid Tractors (6 Series)',
        ];
    }

    public static function forCombinesStatistic(): array
    {
        return [
            'COMBINE HEADS',
            'Combines'
        ];
    }

    public function isCombine(): bool
    {
        return in_array($this->name, self::forCombinesStatistic());
    }
}
