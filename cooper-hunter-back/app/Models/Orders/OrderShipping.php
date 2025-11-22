<?php

namespace App\Models\Orders;

use App\Casts\PhoneCast;
use App\Models\BaseModel;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Traits\HasFactory;
use Database\Factories\Orders\OrderShippingFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property string first_name
 * @property string last_name
 * @property string phone
 * @property int|null state_id
 * @property int|null country_id
 * @property string address_first_line
 * @property string|null address_second_line
 * @property string city
// * @property string state                       // удалить после заливки на прод
// * @property string|null country              // удалить после заливки на прод
 * @property string zip
 * @property string|null trk_number
 * @property int|null order_delivery_type_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see OrderShipping::state()
 * @property-read State state
 *
 * @see OrderShipping::country()
 * @property-read Country country
 *
 * @method static OrderShippingFactory factory(...$parameters)
 */
class OrderShipping extends BaseModel
{
    use HasFactory;

    public const TABLE = 'order_shippings';

    protected $fillable = [
        'order_id',
        'first_name',
        'last_name',
        'phone',
        'address_first_line',
        'address_second_line',
        'city',
        'state_id',
        'country_id',
        'zip',
        'trk_number',
        'order_delivery_type_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'phone' => PhoneCast::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo|Country
    {
        return $this->belongsTo(Country::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(OrderDeliveryType::class, 'order_delivery_type_id', 'id');
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }
}
