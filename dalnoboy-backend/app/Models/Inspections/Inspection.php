<?php

namespace App\Models\Inspections;

use App\Contracts\Models\HasGuard;
use App\Contracts\Models\HasModeration;
use App\Enums\Permissions\GuardsEnum;
use App\Filters\Inspections\InspectionFilter;
use App\Models\BaseModel;
use App\Models\Branches\Branch;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Drivers\Driver;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\InteractsWithMedia;
use App\Traits\Model\ModeratedScopeTrait;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Inspections\InspectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;

/**
 * @see Inspection::inspector()
 * @property-read User inspector
 *
 * @see Inspection::vehicle()
 * @property-read Vehicle vehicle
 *
 * @see Inspection::driver()
 * @property-read Driver driver
 *
 * @see Inspection::inspectionTires()
 * @property-read InspectionTire inspectionTires
 *
 * @method static InspectionFactory factory()
 */
class Inspection extends BaseModel implements HasMedia, HasModeration
{
    use HasFactory;
    use InteractsWithMedia;
    use RuleInTrait;
    use ModeratedScopeTrait;
    use Filterable;

    public const UPDATED_TIME = 259200;
    public const MC_STATE_NUMBER = Vehicle::MC_STATE_NUMBER;
    public const MC_VEHICLE = Vehicle::MC_VEHICLE;
    public const MC_DATA_SHEET_1 = 'data_sheet_1';//72 hours
    public const MC_DATA_SHEET_2 = 'data_sheet_2';
    public const MC_ODO = 'odo';
    public const MC_SIGN = 'sign';

    public const ALLOWED_ORDERED_FIELDS = [
        'created_at'
    ];

    protected $fillable = [
        'inspector_id',
        'branch_id',
        'vehicle_id',
        'driver_id',
        'odo',
        'is_moderated',
        'inspection_reason_id',
        'inspection_reason_description',
        'unable_to_sign',
        'moderation_fields',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_moderated' => 'bool',
        'unable_to_sign' => 'bool',
        'moderation_fields' => 'array'
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(InspectionFilter::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id', 'id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function inspectionReason(): BelongsTo
    {
        return $this->belongsTo(InspectionReason::class, 'inspection_reason_id', 'id');
    }

    public function inspectionTires(): HasMany
    {
        return $this->hasMany(InspectionTire::class, 'inspection_id', 'id');
    }

    public function main(): BelongsTo
    {
        return $this->belongsTo(self::class, 'main_id', 'id');
    }

    public function trailer(): HasOne
    {
        return $this->hasOne(self::class, 'main_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function scopeMy(Builder $builder, User $user): void
    {
        $builder->where('inspector_id', $user->id);
    }

    public function scopeModerated(Builder $builder, HasGuard $guard): void
    {
        if ($guard->getGuard() === GuardsEnum::ADMIN) {
            return;
        }
        $builder->where('is_moderated', true)
            ->orWhere(
                fn(Builder $builder) => $builder->where('is_moderated', false)
                    ->where('inspector_id', $guard->getId())
            );
    }

    public function scopeLinked(Builder $builder, HasGuard $guard): void
    {
        if ($guard->getGuard() === GuardsEnum::ADMIN) {
            return;
        }

        $builder->whereNull('main_id');
    }

    public function shouldModerated(): bool
    {
        if (!empty($this->moderation_fields)) {
            logger('INSPECTION SHOULD MODERATION - 1');
            return true;
        }

        if ($this->driver->shouldModerated()) {
            logger('INSPECTION SHOULD MODERATION - 2');
            return true;
        }

        if ($this->vehicle->shouldModerated()) {
            logger('INSPECTION SHOULD MODERATION - 3');
            return true;
        }

        foreach ($this->inspectionTires as $tire) {
            if (!$tire->shouldModerated()) {
                continue;
            }
            return true;
        }

        return false;
    }

    public function previousVehicleInspection(): ?self
    {
        return $this->vehicle->inspections()->where('id', '<', $this->id)->first();
    }

    public function hasRelation(): ?bool
    {
        return (!empty($this->main_id) || $this->trailer()->exists());
    }
}
