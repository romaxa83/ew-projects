<?php

namespace App\Models\Vehicles\Schemas;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Filters\Vehicles\Schemas\SchemaVehicleFilter;
use App\Models\BaseModel;
use App\Models\Vehicles\Vehicle;
use App\Services\Vehicles\Schemas\SchemaVehicleService;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Vehicles\Schemas\SchemaVehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\JoinClause;

/**
 * @method static SchemaVehicleFactory factory()
 */
class SchemaVehicle extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'schema_vehicles';

    protected $fillable = [
        'name',
        'is_default',
        'vehicle_form',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'vehicle_form' => VehicleFormEnum::class,
        'is_default' => 'bool'
    ];

    protected $appends = [
        'image'
    ];

    public function getImageAttribute(): ?string
    {
        return resolve(SchemaVehicleService::class)
            ->renderSchema($this);
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(SchemaVehicleFilter::class);
    }

    public function axles(): HasMany
    {
        return $this->hasMany(SchemaAxle::class, 'schema_vehicle_id', 'id')
            ->orderBy('position');
    }

    public function wheels(): HasManyThrough
    {
        return $this->hasManyThrough(
            SchemaWheel::class,
            SchemaAxle::class,
            'schema_vehicle_id',
            'schema_axle_id',
            'id',
            'id'
        )
            ->orderBy(SchemaWheel::TABLE . '.id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'schema_id', 'id');
    }

    public function scopeNotDefault(Builder $builder): Builder
    {
        return $builder->where('is_default', false);
    }

    public function scopeDefault(Builder $builder): Builder
    {
        return $builder->where('is_default', true);
    }

    public function scopeVehicleForm(Builder $builder, VehicleFormEnum $form): Builder
    {
        return $builder->where('vehicle_form', $form);
    }

    public function scopeJoinWheels(Builder $builder): Builder
    {
        return $builder->rightJoin(
            SchemaAxle::TABLE,
            fn(JoinClause $joinClause) => $joinClause
                ->on($this->getTable() . '.id', '=', SchemaAxle::TABLE . '.schema_vehicle_id')
        )
            ->rightJoin(
                SchemaWheel::TABLE,
                fn(JoinClause $joinClause) => $joinClause
                    ->on(SchemaAxle::TABLE . '.id', '=', SchemaWheel::TABLE . '.schema_axle_id')
            );
    }
}
