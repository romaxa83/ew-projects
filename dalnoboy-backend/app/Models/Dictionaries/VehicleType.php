<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\VehicleTypeFilter;
use App\Models\BaseModel;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use Database\Factories\Dictionaries\VehicleTypeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static VehicleTypeFactory factory()
 */
class VehicleType extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'vehicle_types';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function vehicleClasses(): BelongsToMany|VehicleClass
    {
        return $this->belongsToMany(VehicleClass::class);
    }

    public function vehicles(): HasMany|Vehicle
    {
        return $this->hasMany(Vehicle::class, 'type_id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(VehicleTypeFilter::class);
    }
}
