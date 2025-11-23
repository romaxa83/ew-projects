<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PeakDate\Type as PeakDateType;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\PeakDate
 *
 * @property int $id
 * @property int $type_id
 * @property string|null $description
 * @property string $date
 * @property-read PeakDateType|null $type
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate query()
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeakDate whereTypeId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class PeakDate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'peaks_dates';
    public $timestamps = false;
    protected $dates = [
        'date' => 'datetime:Y-m-d',
    ];
    protected $with = ['type:id,title,color'];

    public function type(): HasOne
    {
        return $this->hasOne(PeakDateType::class, 'id', 'type_id');
    }
}
