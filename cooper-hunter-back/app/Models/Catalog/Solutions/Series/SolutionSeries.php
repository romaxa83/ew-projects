<?php

namespace App\Models\Catalog\Solutions\Series;

use App\Filters\Catalog\Solutions\SolutionSeriesFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Solutions\Series\SolutionSeriesFactory;

/**
 * @method static SolutionSeriesFactory factory(...$parameters)
 */
class SolutionSeries extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'solution_series';

    public $timestamps = false;

    protected $table = self::TABLE;

    public function modelFilter(): string
    {
        return SolutionSeriesFilter::class;
    }
}
