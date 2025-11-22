<?php

namespace App\Models\Dictionaries;

use App\Contracts\Models\HasModeration;
use App\Filters\Dictionaries\VehicleMakeFilter;
use App\Models\BaseModel;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Dictionaries\VehicleMakeFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static VehicleMakeFactory factory()
 */
class VehicleMake extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'vehicle_makes';

    public const ALLOWED_SORTING_FIELDS = [
        'title',
    ];

    protected $fillable = [
        'title',
        'active',
        'is_moderated',
        'vehicle_form',
    ];

    protected $casts = [
        'active' => 'bool',
        'is_moderated' => 'bool'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(VehicleMakeFilter::class);
    }

    public function vehicles(): HasMany|Vehicle
    {
        return $this->hasMany(Vehicle::class, 'make_id');
    }

    public function vehicleModels(): HasMany|VehicleModel
    {
        return $this->hasMany(VehicleModel::class);
    }

    public function shouldModerated(): bool
    {
        return !$this->isModerated();
    }
}
