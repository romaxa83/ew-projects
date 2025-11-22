<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\TireRelationshipTypeFilter;
use App\Models\BaseModel;
use App\Models\Tires\Tire;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use Database\Factories\Dictionaries\TireRelationshipTypeFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireRelationshipTypeFactory factory()
 */
class TireRelationshipType extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'tire_relationship_types';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireRelationshipTypeFilter::class);
    }

    public function tires(): HasMany|Tire
    {
        return $this->hasMany(Tire::class, 'relationship_type_id');
    }
}
