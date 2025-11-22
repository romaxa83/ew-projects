<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\RecommendationFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Dictionaries\RecommendationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static RecommendationFactory factory()
 */
class Recommendation extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use RuleInTrait;

    public const TABLE = 'recommendations';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(RecommendationFilter::class);
    }

    public function problems(): BelongsToMany|Problem
    {
        return $this->belongsToMany(Problem::class);
    }

    public function regulations(): BelongsToMany|Regulation
    {
        return $this->belongsToMany(Regulation::class);
    }
}
