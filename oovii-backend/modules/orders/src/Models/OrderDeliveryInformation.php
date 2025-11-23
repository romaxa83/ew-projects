<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * \WezomCms\Orders\Models\OrderDeliveryInformation
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $region_code
 * @property string|null $city_code
 * @property string|null $branch_code
 * @property string|null $postal_code
 * @property int|null $tariff_code
 * @property string|null $address
 * @property string|null $time
 * @property string|null $city
 * @property string|null $street
 * @property string|null $house
 * @property int|null $room
 * @property string|null $ttn
 * @property string|null $uuid
 * @property float $delivery_cost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array $delivery_statuses
 *
 * @see OrderDeliveryInformation::order()
 * @property-read Order $order
 *
 * @method static Builder|OrderDeliveryInformation newModelQuery()
 * @method static Builder|OrderDeliveryInformation newQuery()
 * @method static Builder|OrderDeliveryInformation query()
 * @method static Builder|OrderDeliveryInformation whereBranchRef($value)
 * @method static Builder|OrderDeliveryInformation whereCity($value)
 * @method static Builder|OrderDeliveryInformation whereCityRef($value)
 * @method static Builder|OrderDeliveryInformation whereCreatedAt($value)
 * @method static Builder|OrderDeliveryInformation whereHouse($value)
 * @method static Builder|OrderDeliveryInformation whereId($value)
 * @method static Builder|OrderDeliveryInformation whereOrderId($value)
 * @method static Builder|OrderDeliveryInformation whereRoom($value)
 * @method static Builder|OrderDeliveryInformation whereStreet($value)
 * @method static Builder|OrderDeliveryInformation whereTtn($value)
 * @method static Builder|OrderDeliveryInformation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderDeliveryInformation extends Model
{
    use HasFactory;

    public const TABLE = 'order_delivery_information';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'region_code',
        'city_code',
        'branch_code',
        'postal_code',
        'tariff_code',
        'address',
        'time',
        'city',
        'street',
        'house',
        'room',
        'ttn',
        'uuid',
        'delivery_cost',
        'delivery_statuses',
    ];

    protected $casts = [
        'delivery_statuses' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setTtn(string $ttn): self
    {
        $this->ttn = $ttn;

        return $this;
    }

    public function addDeliveryStatus(array $status): self
    {
        $statuses = $this->delivery_statuses ?? [];
        $statuses[] = $status;

        $this->delivery_statuses = $statuses;

        return $this;
    }
}
