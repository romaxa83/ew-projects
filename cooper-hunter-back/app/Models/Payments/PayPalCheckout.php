<?php

namespace App\Models\Payments;

use App\Casts\PriceCast;
use App\Enums\Payments\PaymentReturnPlatformEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use App\Events\Payments\PayPalCheckoutSavedEvent;
use App\Models\BaseModel;
use App\Models\Orders\Order;
use App\Traits\HasFactory;
use Database\Factories\Payments\PayPalCheckoutFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int|null capture_id
 * @property int|null refund_id
 * @property int order_id
 * @property int amount
 * @property string return_platform
 * @property string checkout_status
 * @property string|null refund_status
 * @property string approve_url
 * @property Carbon created_at
 *
 * @method static PayPalCheckoutFactory factory(...$parameters)
 */
class PayPalCheckout extends BaseModel
{
    use HasFactory;

    public const TABLE = 'pay_pal_checkouts';

    public $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'capture_id',
        'refund_id',
        'order_id',
        'amount',
        'return_platform',
        'checkout_status',
        'refund_status',
        'approve_url',
        'created_at',
    ];

    protected $casts = [
        'order_id' => 'int',
        'technician_id' => 'int',
        'amount' => PriceCast::class,
        'return_platform' => PaymentReturnPlatformEnum::class,
        'checkout_status' => PayPalCheckoutStatusEnum::class,
        'refund_status' => PayPalRefundStatusEnum::class,
    ];

    protected $dispatchesEvents = [
        'saved' => PayPalCheckoutSavedEvent::class
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopePlatform(Builder $builder, string $platform): void
    {
        $builder->where('return_platform', $platform);
    }
}
