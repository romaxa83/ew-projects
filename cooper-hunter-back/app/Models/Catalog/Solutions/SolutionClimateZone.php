<?php

namespace App\Models\Catalog\Solutions;

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Models\BaseModel;
use App\Traits\Model\SetSortAfterCreate;
use BenSampo\Enum\Traits\CastsEnums;

class SolutionClimateZone extends BaseModel
{
    use SetSortAfterCreate;
    use CastsEnums;

    public const TABLE = 'solution_climate_zones';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'solution_id',
        'climate_zone'
    ];

    protected $casts = [
        'solution_id' => 'int',
        'climate_zone' => SolutionClimateZoneEnum::class,
    ];
}
