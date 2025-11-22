<?php

namespace App\Models\Orders\Deliveries;

use App\Filters\Orders\DeliveryTypes\OrderDeliveryTypeFilter;
use App\Models\BaseHasTranslation;
use App\Models\Orders\OrderShipping;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveForGuardScopeTrait;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Orders\Deliveries\OrderDeliveryTypeFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static OrderDeliveryTypeFactory factory(...$parameters)
 */
class OrderDeliveryType extends BaseHasTranslation
{
    use HasFactory;
    use SetSortAfterCreate;
    use ActiveScopeTrait;
    use ActiveForGuardScopeTrait;
    use Filterable;

    public const TABLE = 'order_delivery_types';

    protected $fillable = [
        'sort',
        'active',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'sort' => 'int',
        'active' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function modelFilter(): string
    {
        return OrderDeliveryTypeFilter::class;
    }

    public function shippings(): HasMany
    {
        return $this->hasMany(OrderShipping::class, 'order_delivery_type_id', 'id');
    }
}
