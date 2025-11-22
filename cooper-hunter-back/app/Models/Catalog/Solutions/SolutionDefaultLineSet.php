<?php

namespace App\Models\Catalog\Solutions;

use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Solutions\SolutionDefaultLineSetFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SolutionDefaultLineSetFactory factory()
 */
class SolutionDefaultLineSet extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'indoor_id',
        'line_set_id',
        'zone'
    ];

    protected $casts = [
        'indoor_id' => 'int',
        'line_set_id' => 'int',
        'zone' => SolutionZoneEnum::class
    ];

    public function indoor(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'indoor_id', 'id');
    }

    public function lineSet(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'line_set_id', 'id');
    }
}
