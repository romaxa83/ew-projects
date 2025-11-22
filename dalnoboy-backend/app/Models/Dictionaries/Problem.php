<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\ProblemFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use Database\Factories\Dictionaries\ProblemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static ProblemFactory factory()
 */
class Problem extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'problems';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(ProblemFilter::class);
    }

    public function recommendations(): BelongsToMany|Recommendation
    {
        return $this->belongsToMany(Recommendation::class);
    }
}
