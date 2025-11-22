<?php

namespace App\Models\Catalog\Solutions;

use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Filters\Catalog\SolutionFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Catalog\Solutions\SolutionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Solution
 * @package App\Models\Catalog\Solutions
 *
 * @method static SolutionFactory factory(...$options)
 */
class Solution extends BaseModel
{
    use CastsEnums;
    use HasFactory;
    use Filterable;

    public const TABLE = 'solutions';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'product_id',
        'type',
        'short_name',
        'series_id',
        'indoor_type',
        'zone',
        'btu',
        'max_btu_percent',
        'voltage',
    ];

    protected $casts = [
        'product_id' => 'int',
        'series_id' => 'int',
        'type' => SolutionTypeEnum::class,
        'indoor_type' => SolutionIndoorEnum::class,
        'zone' => SolutionZoneEnum::class,
        'btu' => 'int',
        'max_btu_percent' => 'int',
        'voltage' => 'int',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'id'
        );
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(
            SolutionSeries::class,
            'series_id',
            'id'
        );
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            Solution::class,
            'solution_pivot',
            'child_id',
            'parent_id',
            'id',
            'id',
        );
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            Solution::class,
            'solution_pivot',
            'parent_id',
            'child_id',
            'id',
            'id',
        );
    }

    public function climateZones(): HasMany
    {
        return $this->hasMany(
            SolutionClimateZone::class,
            'solution_id',
            'id',
        );
    }

    public function schemas(): HasMany
    {
        return $this->hasMany(SolutionSchema::class, 'outdoor_id', 'id');
    }

    public function defaultLineSets(): HasMany
    {
        return $this->hasMany(SolutionDefaultLineSet::class, 'indoor_id', 'id');
    }

    public function modelFilter(): string
    {
        return SolutionFilter::class;
    }
}
