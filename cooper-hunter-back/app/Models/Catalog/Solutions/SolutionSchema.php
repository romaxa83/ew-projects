<?php

namespace App\Models\Catalog\Solutions;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolutionSchema extends BaseModel
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'pk';

    protected $fillable = [
        'outdoor_id',
        'indoor_id',
        'zone',
        'count_zones',
    ];

    public function indoor(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'indoor_id', 'id');
    }

    public function outdoor(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'outdoor_id', 'id');
    }
}
