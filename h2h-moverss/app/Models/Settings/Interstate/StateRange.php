<?php

namespace App\Models\Settings\Interstate;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Settings\Interstate\StateRange
 *
 * @property int $id
 * @property float $lb_from
 * @property float $lb_to
 * @property float $cbft_from
 * @property float $cbft_to
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange query()
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange volumeRangeId($number)
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange whereCbftFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange whereCbftTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange whereLbFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange whereLbTo($value)
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|StateRange whereDivisionId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class StateRange extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;
    protected $fillable = [
        'division_id',
        'cbft_from',
        'cbft_to',
    ];
    protected $table = 'interstate_state_ranges';

    public function scopeVolumeRangeId($query, $number)
    {
        $number = ceil($number);

        return $query
            ->where([
                ['cbft_from', '<=', $number],
                ['cbft_to', '>=', $number],
            ]);
    }
}
