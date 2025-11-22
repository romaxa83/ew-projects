<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\TireWidthFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTireSizesTrait;
use Database\Factories\Dictionaries\TireWidthFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireWidthFactory factory()
 */
class TireWidth extends BaseModel implements Sortable
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use HasTireSizesTrait;

    public const TABLE = 'tire_widths';

    public const ALLOWED_SORTING_FIELDS = [
        'value',
    ];

    protected $fillable = [
        'value',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireWidthFilter::class);
    }
}
