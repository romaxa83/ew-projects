<?php

namespace App\Models\Order\Estimate;

use Database\Factories\Orders\EstimateCalculatedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order\Estimate\Calculated
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $estimate_type
 * @property string|null $title
 * @property string|null $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string $description
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated interstate()
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated intrastate()
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated local()
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated query()
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereEstimateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calculated whereValue($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static EstimateCalculatedFactory factory(...$parameters)
 */
class Calculated extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-estimate-calculated';

    public const TABLE = 'orders_estimates_calculated';
    protected $table = self::TABLE;

    protected $fillable = [
        'order_id',
        'estimate_type',
        'title',
        'value'
    ];
    protected $appends = ['description'];


    public function getDescriptionAttribute(): string
    {
        return array_key_exists($this->title, config('app.calculated_table')) ? config('app.calculated_table')[$this->title]['description'] : '';
    }

    public function scopeLocal($query)
    {
        return $query->where('estimate_type', 'local');
    }

    public function scopeInterstate($query)
    {
        return $query->where('estimate_type', 'interstate');
    }

    public function scopeIntrastate($query)
    {
        return $query->where('estimate_type', 'intrastate');
    }

    protected static function newFactory(): EstimateCalculatedFactory
    {
        return EstimateCalculatedFactory::new();
    }
}
