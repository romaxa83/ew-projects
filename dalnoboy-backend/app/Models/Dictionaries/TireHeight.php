<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\TireHeightFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTireSizesTrait;
use Database\Factories\Dictionaries\TireHeightFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireHeightFactory factory()
 */
class TireHeight extends BaseModel implements Sortable
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use HasTireSizesTrait;

    public const TABLE = 'tire_heights';

    public const ALLOWED_SORTING_FIELDS = [
        'value',
    ];

    protected $fillable = [
        'value',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireHeightFilter::class);
    }
}
