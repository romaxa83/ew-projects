<?php

namespace App\Models\Calculation;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Calculation\IntrastateRatesDistance
 *
 * @property int $id
 * @property int $from
 * @property int $to
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance search($number)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance whereTo($value)
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesDistance whereDivisionId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class IntrastateRatesDistance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;
    protected $fillable = [
        'division_id',
        'from',
        'to',
    ];

    public function scopeSearch($query, $number)
    {
        $number = ceil($number);

        return $query
            ->where([
                ['from', '<=', $number - 1],
                ['to', '>=', $number],
            ])
            ->orWhere(function ($query) use ($number) {
                $query->where('from', '<=', $number)
                    ->where('to', '=', 0);
            });
    }
}
