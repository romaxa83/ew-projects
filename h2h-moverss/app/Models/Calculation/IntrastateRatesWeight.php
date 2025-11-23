<?php

namespace App\Models\Calculation;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Calculation\IntrastateRatesWeight
 *
 * @property int $id
 * @property int $from
 * @property int $to
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight search($number)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight whereTo($value)
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|IntrastateRatesWeight whereDivisionId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class IntrastateRatesWeight extends Model implements Auditable
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
                ['from', '<=', $number],
                ['to', '>', $number + 1],
            ])
            ->orWhere(function ($query) use ($number) {
                $query->where('from', '<=', $number)
                    ->where('to', '=', 0);
            });
    }
}
