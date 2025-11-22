<?php

namespace App\Models\Vehicles;

use App\Casts\CoveCaseCast;
use App\Contracts\Models\HasModeration;
use App\Enums\Vehicles\VehicleFormEnum;
use App\Filters\Vehicles\VehicleFilter;
use App\Models\BaseModel;
use App\Models\Clients\Client;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use App\Models\Dictionaries\VehicleType;
use App\Models\Inspections\Inspection;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\InteractsWithMedia;
use App\Traits\Model\ModeratedScopeTrait;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Vehicles\VehicleFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

/**
 * @method static VehicleFactory factory()
 */
class Vehicle extends BaseModel implements HasMedia, HasModeration
{
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;
    use InteractsWithMedia;
    use RuleInTrait;

    public const TABLE = 'vehicles';

    public const MC_STATE_NUMBER = 'state_number';
    public const MC_VEHICLE = 'vehicle';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'created_at',
        'state_number'
    ];

    protected $fillable = [
        'state_number',
        'vin',
        'is_moderated',
        'form',
        'class_id',
        'type_id',
        'make_id',
        'model_id',
        'client_id',
        'schema_id',
        'odo',
        'active',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'state_number' => CoveCaseCast::class,
        'vin' => CoveCaseCast::class,
        'is_moderated' => 'bool',
        'form' => VehicleFormEnum::class,
        'class_id' => 'int',
        'type_id' => 'int',
        'make_id' => 'int',
        'model_id' => 'int',
        'client_id' => 'int',
        'schema_id' => 'int',
        'odo' => 'int',
        'active' => 'bool',
    ];

    public function vehicleClass(): BelongsTo
    {
        return $this->belongsTo(VehicleClass::class, 'class_id', 'id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'type_id', 'id');
    }

    public function vehicleMake(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id', 'id');
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function schemaVehicle(): BelongsTo
    {
        return $this->belongsTo(SchemaVehicle::class, 'schema_id', 'id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'vehicle_id', 'id')
            ->orderByDesc('id');
    }

    public function lastInspection(): Inspection|HasMany|null
    {
        return $this->inspections()
            ->first();
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(VehicleFilter::class);
    }

    public function shouldModerated(): bool
    {
        if (!$this->isModerated()) {
            return true;
        }

        if ($this->vehicleMake->shouldModerated()) {
            return true;
        }

        if ($this->vehicleModel->shouldModerated()) {
            return true;
        }

        if ($this->client->shouldModerated()) {
            return true;
        }

        return false;
    }
}
