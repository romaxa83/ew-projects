<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\TireTypeFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use Database\Factories\Dictionaries\TireTypeFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireTypeFactory factory()
 */
class TireType extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'tire_types';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireTypeFilter::class);
    }

    public function tireSpecifications(): HasMany|TireSpecification
    {
        return $this->hasMany(TireSpecification::class, 'type_id');
    }
}
