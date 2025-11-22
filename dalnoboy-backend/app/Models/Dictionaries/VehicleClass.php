<?php

namespace App\Models\Dictionaries;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Filters\Dictionaries\VehicleClassFilter;
use App\Models\BaseModel;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use Database\Factories\Dictionaries\VehicleClassFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static VehicleClassFactory factory()
 */
class VehicleClass extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'vehicle_classes';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $fillable = [
        'vehicle_form',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'vehicle_form' => VehicleFormEnum::class,
    ];

    public function vehicleTypes(): BelongsToMany|VehicleClass
    {
        return $this->belongsToMany(VehicleType::class);
    }

    public function vehicles(): HasMany|Vehicle
    {
        return $this->hasMany(Vehicle::class, 'class_id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(VehicleClassFilter::class);
    }
}
