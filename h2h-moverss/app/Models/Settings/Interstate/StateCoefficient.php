<?php

namespace App\Models\Settings\Interstate;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Settings\Interstate\StateCoefficient
 *
 * @property int $id
 * @property int $range_id
 * @property string $from_state
 * @property string $to_state
 * @property string $price
 * @property string|null $price_2
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient query()
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient whereFromState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient wherePrice2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient whereRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient whereToState($value)
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|StateCoefficient whereDivisionId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class StateCoefficient extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'interstate_state_coefficients';
    public $timestamps = false;
    protected $fillable = [
        'division_id',
        'range_id',
        'from_state',
        'to_state',
        'price',
    ];

    public function findVolumeRate($volume, $fromState, $toState, $divisionID)
    {
        $range = StateRange::where('division_id', $divisionID)->volumeRangeId($volume)->first(['id']);
        // Надо генерить информационные эксепшены?
        // TODO https://stackoverflow.com/questions/58690463/best-way-to-store-error-messages-in-laravel-session-or-variable
//            throw new Exception('IntrastateRatesWeight can\'t find - w:' . $volume);
        if (!$range)
            throw new \Exception('Interstate rate range for volume "' . $volume . " cbFt\" not found!");
        $record = $this
            ->where('range_id', $range->id)
            ->where('from_state', $fromState)
            ->where('to_state', $toState)
            ->where('division_id', $divisionID)
            ->first(['price']);
        if (!$record)
            throw new \Exception('Interstate rate for volume "' . $volume . " cbFt\" between \"{$fromState}\" and \"{$toState}\" not found!");
        return $record->price;

    }
}
