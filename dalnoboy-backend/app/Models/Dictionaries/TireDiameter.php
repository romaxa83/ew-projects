<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\TireDiameterFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTireSizesTrait;
use Database\Factories\Dictionaries\TireDiameterFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireDiameterFactory factory()
 */
class TireDiameter extends BaseModel implements Sortable
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use HasTireSizesTrait;

    public const TABLE = 'tire_diameters';

    public const ALLOWED_SORTING_FIELDS = [
        'value',
    ];

    protected $fillable = [
        'value',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireDiameterFilter::class);
    }
}
