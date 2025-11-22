<?php

namespace App\Models\Statistics;

use App\Casts\Statistics\Solutions\IndoorsCast;
use App\Collections\Statistics\Solutions\IndoorsCollection;
use App\Entities\Statistics\Solutions\IndoorEntity;
use App\Filters\Statistics\FindSolutionStatisticFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Statistics\FindSolutionStatisticFactory;

/**
 * @property string outdoor Outdoor unit title
 * @property string outdoor_btu
 * @property string outdoor_voltage
 * @property string climate_zone
 * @property string series
 * @property IndoorsCollection|IndoorEntity[] indoors
 *
 * @method static FindSolutionStatisticFactory factory(...$parameters)
 */
class FindSolutionStatistic extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'find_solution_statistics';

    protected $table = self::TABLE;

    protected $casts = [
        'indoors' => IndoorsCast::class,
    ];

    public function modelFilter(): string
    {
        return FindSolutionStatisticFilter::class;
    }
}
