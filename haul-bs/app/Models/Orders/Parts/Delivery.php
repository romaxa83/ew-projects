<?php

namespace App\Models\Orders\Parts;

use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\DeliveryStatus;
use App\Foundations\Models\BaseModel;
use App\Services\DeliveryServices\Drivers\AbstractDeliveryDriver;
use App\Services\DeliveryServices\Drivers\DeliveryDriver;
use App\Services\DeliveryServices\Drivers\Fedex;
use App\Services\DeliveryServices\Drivers\Ups;
use App\Services\DeliveryServices\Drivers\Usps;
use Database\Factories\Orders\Parts\DeliveryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property float cost
 * @property DeliveryMethod method
 * @property string|null tracking_number
 * @property Carbon|null sent_at
 * @property DeliveryStatus status
 *
 * @see Order::order()
 * @property Order|BelongsTo order
 *
 * @mixin Eloquent
 *
 * @method static DeliveryFactory factory(...$parameters)
 */
class Delivery extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'parts_order_deliveries';
    protected $table = self::TABLE;

    /**@var array<int, string>*/
    protected $fillable = [];

    protected $casts = [
        'method' => DeliveryMethod::class,
        'status' => DeliveryStatus::class,
        'sent_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function dataForUpdateHistory(): array
    {
        $old = $this->getAttributes();

        return $old;
    }

    public function getDriver(): ?AbstractDeliveryDriver
    {
        return match ($this->method) {
            DeliveryMethod::UPS => new Ups($this),
            DeliveryMethod::Fedex => new Fedex($this),
            DeliveryMethod::USPS => new Usps($this),
            default => null,
        };
    }

    public function getServiceStatus(): DeliveryStatus
    {
        $driver = $this->getDriver();

        return $driver ? $driver->mapToOrderDeliveryStatus() : $this->status;
    }
}
