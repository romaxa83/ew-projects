<?php

namespace App\Models\Vehicles\Schemas;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchemaAxle extends BaseModel
{
    use HasFactory;

    public const TABLE = 'schema_axles';

    protected $fillable = [
        'position',
        'name',
        'need_add',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'need_add' => 'bool'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function schemaVehicle(): BelongsTo
    {
        return $this->belongsTo(SchemaAxle::class, 'schema_vehicle_id', 'id');
    }

    public function wheels(): HasMany
    {
        return $this->hasMany(SchemaWheel::class, 'schema_axle_id', 'id')
            ->orderBy('position');
    }
}
