<?php

namespace App\Models\Calculation;

use Illuminate\Database\Eloquent\Model;
use Exception;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Calculation\IntrastateRates
 *
 * @property int $id
 * @property int $rate_weight_id
 * @property int $rate_distance_id
 * @property float $coefficient
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates whereCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates whereRateDistanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates whereRateWeightId($value)
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRates whereDivisionId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Calculation\IntrastateRatesDistance|null $distanceRange
 * @property-read \App\Models\Calculation\IntrastateRatesWeight|null $weightRange
 * @mixin \Eloquent
 */
class IntrastateRates extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;
    protected $fillable = [
        'division_id',
        'rate_weight_id',
        'rate_distance_id',
        'coefficient',
    ];


    public function weightRange()
    {
        return $this->hasOne(IntrastateRatesWeight::class, 'id','rate_weight_id');
    }

    public function distanceRange()
    {
        return $this->hasOne(IntrastateRatesDistance::class, 'id', 'rate_distance_id');
    }


    public function findRate($weight, $distance, $division_id)
    {
        $weight_id = IntrastateRatesWeight::where('division_id', $division_id)->search($weight)->first(['id']);
        if (!$weight_id) {
            throw new Exception('IntrastateRatesWeight range for weight: "' . $weight . ' lbs" not found!');
        }

        $distance_id = IntrastateRatesDistance::where('division_id', $division_id)->search($distance)->first(['id']);
        if (!$distance_id) {
            throw new Exception('IntrastateRatesDistance range for distance: "' . $distance . ' mi" not found!');
        }


        $res = $this
            ->where('division_id', $division_id)
            ->where('rate_weight_id', $weight_id->id)
            ->where('rate_distance_id', $distance_id->id)
            ->first(['coefficient']);
        if (!$res) {
            throw new Exception('IntrastateRates rate for weight: "' . $weight . ' lbs", distance: "' . $distance . ' mi" not found!');
        }

        return $res->coefficient;
    }
}
