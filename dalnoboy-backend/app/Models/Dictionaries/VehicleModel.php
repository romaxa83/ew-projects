<?php

namespace App\Models\Dictionaries;

use App\Contracts\Models\HasModeration;
use App\Filters\Dictionaries\VehicleModelFilter;
use App\Models\BaseModel;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Dictionaries\VehicleModelFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static VehicleModelFactory factory()
 */
class VehicleModel extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'vehicle_models';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $fillable = [
        'title',
        'active',
        'is_moderated',
        'vehicle_make_id',
    ];

    protected $casts = [
        'active' => 'bool',
        'is_moderated' => 'bool',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function vehicleMake(): BelongsTo|VehicleMake|null
    {
        return $this->belongsTo(VehicleMake::class);
    }

    public function vehicles(): HasMany|Vehicle
    {
        return $this->hasMany(Vehicle::class, 'model_id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(VehicleModelFilter::class);
    }

    public function shouldModerated(): bool
    {
        return !$this->isModerated();
    }
}
