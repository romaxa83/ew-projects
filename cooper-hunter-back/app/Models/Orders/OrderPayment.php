<?php

namespace App\Models\Orders;

use App\Casts\PriceCast;
use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use App\Models\BaseModel;
use App\Models\Payments\PayPalCheckout;
use App\Traits\HasFactory;
use Database\Factories\Orders\OrderPaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int id
 * @property int order_id
 * @property int|null order_price
 * @property int|null order_price_with_discount
 * @property int|null shipping_cost
 * @property int|null tax
 * @property int|null discount
 * @property int|null paid_at
 * @property int|null refund_at
 * @property Carbon|null create_at
 * @property Carbon|null updated_at
 *
 * @method static OrderPaymentFactory factory(...$parameters)
 */
class OrderPayment extends BaseModel
{
    use HasFactory;

    public const TABLE = 'order_payments';

    protected $fillable = [
        'order_id',
        'order_price',
        'order_price_with_discount',
        'shipping_cost',
        'tax',
        'discount',
        'paid_at',
        'refund_at',
        'updated_at',
        'created_at'
    ];

    protected $casts = [
        'order_price' => PriceCast::class,
        'order_price_with_discount' => PriceCast::class,
        'shipping_cost' => PriceCast::class,
        'tax' => PriceCast::class,
        'discount' => PriceCast::class,
        'updated_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(PayPalCheckout::class, 'order_id', 'order_id');
    }

    public function scopeCostStatus(Builder|self $builder): void
    {
        $builder->leftJoinSub(
            DB::table(PayPalCheckout::TABLE)
                ->selectRaw(
                    "order_id,
                    MAX(
                        CASE
                            WHEN refund_status = ? THEN 1
                            ELSE 0
                        END
                    ) AS refund_pending,
                    MAX(
                        CASE
                            WHEN refund_status = ? THEN 1
                            ELSE 0
                        END
                    ) AS refund_canceled,
                    MAX(
                        CASE
                            WHEN refund_status = ? THEN 1
                            ELSE 0
                        END
                    ) AS refund_complete,
                    MAX(
                        CASE
                            WHEN checkout_status = ? THEN 1
                            ELSE 0
                        END
                    ) AS checkout_approved",
                    [
                        PayPalRefundStatusEnum::PENDING,
                        PayPalRefundStatusEnum::CANCELLED,
                        PayPalRefundStatusEnum::COMPLETED,
                        PayPalCheckoutStatusEnum::APPROVED,
                    ]
                )
                ->groupBy(PayPalCheckout::TABLE . '.order_id'),
            PayPalCheckout::TABLE,
            fn(JoinClause $join) => $join->on(
                OrderPayment::TABLE . '.order_id',
                '=',
                PayPalCheckout::TABLE . '.order_id'
            )
        )
            ->selectRaw(
                OrderPayment::TABLE . ".*,
                CASE
                    WHEN paid_at IS NOT NULL AND refund_at IS NULL THEN
                        CASE
                            WHEN refund_pending = 1 THEN ?
                            ELSE ?
                        END
                    WHEN refund_at IS NOT NULL THEN ?
                    WHEN checkout_approved = 1 THEN ?
                    WHEN order_price IS NOT NULL THEN ?
                    ELSE ?
                END as cost_status
            ",
                [
                    OrderCostStatusEnum::REFUND_IN_PROCESS,
                    OrderCostStatusEnum::PAID,
                    OrderCostStatusEnum::REFUND_COMPLETE,
                    OrderCostStatusEnum::PAYMENT_IN_PROCESS,
                    OrderCostStatusEnum::WAITING_TO_PAY,
                    OrderCostStatusEnum::NOT_FORMED
                ]
            );
    }
}
