<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use App\Models\Service as ServiceParent;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Service
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $service_id
 * @property string $title
 * @property float $price
 * @property int $qty
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read ServiceParent|null $service
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Service extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'orders_services';

    public function service(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ServiceParent::class, 'id', 'service_id');
    }
}
