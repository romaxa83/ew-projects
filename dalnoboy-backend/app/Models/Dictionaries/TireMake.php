<?php

namespace App\Models\Dictionaries;

use App\Contracts\Models\HasModeration;
use App\Filters\Dictionaries\TireMakeFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Dictionaries\TireMakeFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireMakeFactory factory()
 */
class TireMake extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'tire_makes';

    public const ALLOWED_SORTING_FIELDS = [
        'title',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireMakeFilter::class);
    }

    public function tireModels(): HasMany|TireModel
    {
        return $this->hasMany(TireModel::class);
    }

    public function tireSpecifications(): HasMany|TireSpecification
    {
        return $this->hasMany(TireSpecification::class, 'make_id');
    }

    public function shouldModerated(): bool
    {
        return !$this->isModerated();
    }
}
