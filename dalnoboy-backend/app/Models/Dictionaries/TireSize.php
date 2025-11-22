<?php

namespace App\Models\Dictionaries;

use App\Contracts\Models\HasModeration;
use App\Filters\Dictionaries\TireSizeFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Dictionaries\TireSizeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireSizeFactory factory()
 */
class TireSize extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'tire_sizes';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $fillable = [
        'tire_width_id',
        'tire_height_id',
        'tire_diameter_id',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireSizeFilter::class);
    }

    public function tireWidth(): BelongsTo|TireWidth
    {
        return $this->belongsTo(TireWidth::class);
    }

    public function tireHeight(): BelongsTo|TireHeight
    {
        return $this->belongsTo(TireHeight::class);
    }

    public function tireDiameter(): BelongsTo|TireDiameter
    {
        return $this->belongsTo(TireDiameter::class);
    }

    public function tireSpecifications(): HasMany|TireSpecification
    {
        return $this->hasMany(TireSpecification::class, 'size_id');
    }

    public function shouldModerated(): bool
    {
        return !$this->isModerated();
    }
}
