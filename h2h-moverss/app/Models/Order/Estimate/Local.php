<?php

namespace App\Models\Order\Estimate;

use Database\Factories\Orders\EstimateLocalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Estimate\Local
 *
 * @property int $order_id
 * @property float $hours_min
 * @property float $hours_max
 * @property float|null $rate
 * @property int|null $rate_auto
 * @property int $is_auto
 * @property string|null $mileage
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Local newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Local newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Local query()
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereHoursMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereHoursMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereRateAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Local whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static EstimateLocalFactory factory(...$parameters)
 */
class Local extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-estimate-local';

    public const TABLE = 'orders_estimates_local';
    protected $table = self::TABLE;
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'hours_min',
        'hours_max',
        'rate',
        'is_auto',
        'rate_auto'
    ];

    protected $casts = [
        'hours_min' => 'float',
        'hours_max' => 'float',
        'rate' => 'float',
    ];

    protected static function newFactory(): EstimateLocalFactory
    {
        return EstimateLocalFactory::new();
    }
}
