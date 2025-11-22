<?php

namespace App\Models\Inspections;

use App\Models\Dictionaries\Problem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InspectionTireProblem extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'inspection_tire_id',
        'problem_id',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'problem_id', 'id');
    }

    public function inspectionTire(): BelongsTo
    {
        return $this->belongsTo(InspectionTire::class, 'inspection_tire_id', 'id');
    }
}
