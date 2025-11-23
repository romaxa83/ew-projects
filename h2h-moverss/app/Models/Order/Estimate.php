<?php

namespace App\Models\Order;

use Database\Factories\Orders\EstimateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order\Estimate
 *
 * @property int $order_id
 * @property string $type
 * @property int $is_locked
 * @property int|null $trucks
 * @property int|null $crews
 * @property string $discount_type
 * @property float|null $discount_value
 * @property string $fee_type
 * @property float|null $travel_fee
 * @property float|null $calculated_moving_min_value
 * @property float|null $calculated_moving_max_value
 * @property float|null $calculated_extra_services
 * @property float|null $calculated_extra_materials
 * @property int|null $calculated_moving_time Секунды
 * @property float|null $calculated_moving_distance Miles
 * @property float|null $calculated_moving_distance_auto Miles
 * @property int $calculated_moving_distance_is_auto
 * @property int $dispatch_allowed
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Order\Estimate\Interstate|null $interstate
 * @property-read \App\Models\Order\Estimate\Intrastate|null $intrastate
 * @property-read \App\Models\Order\Estimate\Local|null $local
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedExtraMaterials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedExtraServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedMovingDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedMovingDistanceAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedMovingDistanceIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedMovingMaxValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedMovingMinValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculatedMovingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCrews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDispatchAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereFeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereTravelFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereTrucks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static EstimateFactory factory(...$parameters)
 */
class Estimate extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-estimate';

    public const TABLE = 'orders_estimates';
    protected $table = self::TABLE;
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'order_id',
        'type',
        'is_locked',
        'trucks',
        'crews',
        'discount_value',
        'discount_type',
        'travel_fee',
        'calculated_moving_min_value',
        'calculated_moving_max_value',
        'fee_type',
        'calculated_moving_time',
        'calculated_moving_distance',
        'calculated_moving_distance_auto',
        'calculated_moving_distance_is_auto'
    ];

    protected $casts = [
        'discount_value' => 'float',
        'travel_fee' => 'float',
        'calculated_moving_min_value' => 'float',
        'calculated_moving_max_value' => 'float',
        'calculated_extra_services' => 'float',
        'calculated_extra_materials' => 'float',
        'calculated_moving_distance' => 'float',
        'calculated_moving_distance_auto' => 'float',
    ];

    protected static function newFactory(): EstimateFactory
    {
        return EstimateFactory::new();
    }

    public function local(): HasOne
    {
        return $this->hasOne(Estimate\Local::class, 'order_id', 'order_id');
    }

    public function interstate(): HasOne
    {
        return $this->hasOne(Estimate\Interstate::class, 'order_id', 'order_id');
    }

    public function intrastate(): HasOne
    {
        return $this->hasOne(Estimate\Intrastate::class, 'order_id', 'order_id');
    }

    public function getCalculatedMovingMinValueAttribute()
    {
        return $this->attributes['calculated_moving_min_value'] + 0;
    }

    public function getCalculatedMovingMaxValueAttribute()
    {
        return $this->attributes['calculated_moving_max_value'] + 0;
    }

}
