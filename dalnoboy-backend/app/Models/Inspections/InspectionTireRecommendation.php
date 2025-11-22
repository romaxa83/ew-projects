<?php

namespace App\Models\Inspections;

use App\Models\Dictionaries\Recommendation;
use App\Models\Tires\Tire;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InspectionTireRecommendation extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'inspection_tire_id',
        'recommendation_id',
        'new_tire_id',
        'is_confirmed',
    ];

    protected $casts = [
        'is_confirmed' => 'bool'
    ];

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class, 'recommendation_id', 'id');
    }

    public function inspectionTire(): BelongsTo
    {
        return $this->belongsTo(InspectionTire::class, 'inspection_tire_id', 'id');
    }

    public function newTire(): BelongsTo
    {
        return $this->belongsTo(Tire::class, 'tire_id', 'id');
    }
}
