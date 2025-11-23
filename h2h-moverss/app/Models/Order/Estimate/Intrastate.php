<?php

namespace App\Models\Order\Estimate;

use Database\Factories\Orders\EstimateIntrastateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Estimate\Intrastate
 *
 * @property int $order_id
 * @property float|null $rate
 * @property float|null $rate_auto
 * @property int $is_auto
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate whereIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate whereRateAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intrastate whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static EstimateIntrastateFactory factory(...$parameters)
 */
class Intrastate extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-estimate-intrastate';

    public const TABLE = 'orders_estimates_intrastate';
    protected $table = self::TABLE;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'rate',
        'rate_auto',
        'is_auto'
    ];

    protected $casts = [
        'rate' => 'float',
        'rate_auto' => 'float',
    ];

    protected static function newFactory(): EstimateIntrastateFactory
    {
        return EstimateIntrastateFactory::new();
    }
}
