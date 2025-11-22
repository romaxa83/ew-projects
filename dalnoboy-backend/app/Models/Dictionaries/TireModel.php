<?php

namespace App\Models\Dictionaries;

use App\Contracts\Models\HasModeration;
use App\Filters\Dictionaries\TireModelFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Dictionaries\TireModelFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireModelFactory factory()
 */
class TireModel extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'tire_models';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $fillable = [
        'tire_make_id',
    ];

    public function tireMake(): BelongsTo|TireMake|null
    {
        return $this->belongsTo(TireMake::class);
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(TireModelFilter::class);
    }

    public function tireSpecifications(): HasMany|TireSpecification
    {
        return $this->hasMany(TireSpecification::class, 'model_id');
    }

    public function shouldModerated(): bool
    {
        return !$this->isModerated();
    }
}
