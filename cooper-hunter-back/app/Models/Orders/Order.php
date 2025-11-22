<?php

namespace App\Models\Orders;

use App\Casts\PhoneCast;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Roles\HasGuardUser;
use App\Enums\Orders\OrderStatusEnum;
use App\Events\Orders\OrderDeletedEvent;
use App\Events\Orders\OrderSavedEvent;
use App\Filters\Orders\OrderFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Payments\PayPalCheckout;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int technician_id
 * @property int|null project_id
 * @property string status
 * @property int|null product_id
 * @property string|null serial_number
 * @property string first_name
 * @property string last_name
 * @property string phone
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property string|null comment
 *
 * @method static OrderFactory factory(...$parameters)
 */
class Order extends BaseModel implements AlertModel
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;

    public const TABLE = 'orders';

    public const MORPH_NAME = 'order';

    protected $fillable = [
        'technician_id',
        'status',
        'product_id',
        'serial_number',
        'first_name',
        'last_name',
        'phone',
        'comment',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'phone' => PhoneCast::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'saved' => OrderSavedEvent::class,
        'deleted' => OrderDeletedEvent::class,
    ];

    public function ticket(): BelongsTo|Ticket
    {
        return $this->belongsTo(Ticket::class);
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(OrderPart::class, 'order_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(OrderShipping::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(OrderPayment::class)
            ->scopes('costStatus');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'id');
    }

    public function checkouts(): HasMany|PayPalCheckout
    {
        return $this->hasMany(PayPalCheckout::class, 'order_id', 'id');
    }

    public function modelFilter(): string
    {
        return OrderFilter::class;
    }

    public function scopeForGuard(Builder|self $build, HasGuardUser $user): void
    {
        if (!$user instanceof Technician) {
            return;
        }

        $build->where('technician_id', $user->getId());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function scopeActive(Builder|self $build): void
    {
        $build->whereNotIn('status', [OrderStatusEnum::SHIPPED, OrderStatusEnum::CANCELED]);
    }

    public function scopeHistory(Builder|self $build): void
    {
        $build->whereIn('status', [OrderStatusEnum::SHIPPED, OrderStatusEnum::CANCELED]);
    }

    public function scopeCostNotFormed(Builder|self $build): void
    {
        $build->whereHas(
            'payment',
            fn(Builder $builder) => $builder->whereNull('order_price')
        );
    }

    public function scopeCostWaitingToPay(Builder|self $build): void
    {
        $build->whereHas(
            'payment',
            fn(Builder $builder) => $builder->whereNotNull('order_price')
                ->whereNull('paid_at')
                ->whereNull('refund_at')
        );
    }

    public function scopeCostPaid(Builder|self $build): void
    {
        $build->whereHas(
            'payment',
            fn(Builder $builder) => $builder->whereNotNull('paid_at')
                ->whereNull('refund_at')
        );
    }

    public function scopeCostRefund(Builder|self $build): void
    {
        $build->whereHas(
            'payment',
            fn(Builder $builder) => $builder->whereNotNull('paid_at')
                ->whereNotNull('refund_at')
        );
    }

    public function scopeWithTrkNumber(Builder|self $build): void
    {
        $build->whereHas(
            'shipping',
            fn(Builder $builder) => $builder->whereNotNull('trk_number')
        );
    }

    public function scopeTrkNumberNotAssigned(Builder|self $build): void
    {
        $build->whereHas(
            'shipping',
            fn(Builder $builder) => $builder->whereNull('trk_number')
        );
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }
}
