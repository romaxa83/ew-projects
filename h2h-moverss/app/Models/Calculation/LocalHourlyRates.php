<?php

namespace App\Models\Calculation;

use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Calculation\LocalHourlyRates
 *
 * @property int $id
 * @property int $workday
 * @property int $holiday
 * @property int $peakday
 * @property int|null $crew_qty
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates query()
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates whereCrewQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates whereHoliday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates wherePeakday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates whereWorkday($value)
 * @property int|null $division_id
 * @property string|null $season
 * @property-read Division|null $division
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalHourlyRates whereSeason($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class LocalHourlyRates extends Model implements Auditable
{
    use AuditableTrait;

    public const SEASON_WINTER = 'winter';
    public const SEASON_SUMMER = 'summer';

    public const TABLE = 'local_hourly_rates';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'crew_qty',
        'workday',
        'holiday',
        'peakday',
        'division_id',
        'season'
    ];

    public function division()
    {
        return $this->hasOne(Division::class, 'id', 'division_id');
    }
}
