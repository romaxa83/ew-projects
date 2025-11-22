<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\RegulationFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use Database\Factories\Dictionaries\RegulationFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static RegulationFactory factory()
 */
class Regulation extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'regulations';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(RegulationFilter::class);
    }
}
