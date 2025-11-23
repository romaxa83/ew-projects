<?php

namespace App\Models\Order\Estimate;

use Database\Factories\Orders\EstimateInterstateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Estimate\Interstate
 *
 * @property int $order_id
 * @property string $estimate_rate
 * @property float|null $rate
 * @property float|null $rate_auto
 * @property int $is_auto
 * @property float|null $packing
 * @property float|null $unpacking
 * @property int $shuttle_pickup
 * @property int $shuttle_delivery
 * @property string|null $delivery_days
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereEstimateRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate wherePacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereRateAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereShuttleDelivery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereShuttlePickup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereUnpacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interstate whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static EstimateInterstateFactory factory(...$parameters)
 */
class Interstate extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-estimate-interstate';

    public const TABLE = 'orders_estimates_interstate';
    protected $table = self::TABLE;
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'order_id',
        'estimate_rate',
        'rate',
        'rate_auto',
        'is_auto',
        'packing',
        'unpacking',
        'shuttle_pickup',
        'shuttle_delivery',
        'delivery_days'
    ];

    protected $casts = [
        'rate' => 'float',
        'rate_auto' => 'float',
        'packing' => 'float',
        'unpacking' => 'float',
    ];

    protected static function newFactory(): EstimateInterstateFactory
    {
        return EstimateInterstateFactory::new();
    }
}
