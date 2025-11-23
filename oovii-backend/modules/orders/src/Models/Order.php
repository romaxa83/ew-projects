<?php

namespace WezomCms\Orders\Models;

use Cache;
use DB;
use Eloquent;
use Greabock\Tentacles\EloquentTentacle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use WezomCms\Core\Contracts\BelongsToAdminInterface;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Scopes\BelongsToAdminScope;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Orders\Contracts\ClientData;
use WezomCms\Orders\Contracts\OnlinePaymentInterface;
use WezomCms\Providers\Models\Provider;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

/**
 * \WezomCms\Orders\Models\Order
 *
 * @property int $id
 * @property int|null $delivery_id
 * @property int|null $payment_id
 * @property int|null $status_id
 * @property int $provider_id
 * @property int|null $user_id
 * @property int|null $amocrm_lead_id
 * @property bool $payed
 * @property bool $dont_call_back
 * @property string|null $payed_mode
 * @property Carbon|null $payed_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrderClient|null $client
 * @property-read Delivery|null $delivery
 * @property-read string|null $delivery_address
 * @property-read OrderDeliveryInformation|null $deliveryInformation
 * @property-read string $currency_iso_code
 * @property-read mixed $discount
 * @property-read string $quantity
 * @property-read float $whole_price
 * @property-read float $whole_purchase_price
 * @property-read Collection|OrderItem[] $items
 * @property-read int|null $items_count
 * @property-read Payment|null $payment
 * @property-read OrderRecipient|null $recipient
 * @property-read OrderStatus|null $status
 * @property-read Collection|OrderStatus[] $statusHistory
 * @property-read int|null $status_history_count
 * @property-read User|null $user
 *
 * @see Order::provider()
 * @property-read Provider $provider
 *
 * @see Order::paymentInformation()
 * @property-read OrderPaymentInformation $paymentInformation
 *
 * @see Order::useBonusHistory()
 * @property-read Collection|BonusHistory $useBonusHistory
 *
 * @see Order::administrator()
 * @property-read Administrator|null $administrator
 *
 * @method static Builder|Order filter(array $input = [], $filter = null)
 * @method static Builder|Order new()
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order onlyTrashed()
 * @method static Builder|Order paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Order query()
 * @method static Builder|Order simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|Order whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDeliveryId($value)
 * @method static Builder|Order whereDiscountType($value)
 * @method static Builder|Order whereDontCallBack($value)
 * @method static Builder|Order whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereLike(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Order wherePayed($value)
 * @method static Builder|Order wherePayedAt($value)
 * @method static Builder|Order wherePayedMode($value)
 * @method static Builder|Order wherePaymentId($value)
 * @method static Builder|Order wherePromoCodeAmount($value)
 * @method static Builder|Order wherePromoCodeId($value)
 * @method static Builder|Order whereStatusId($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @method static Builder|Order withTrashed()
 * @method static Builder|Order withoutTrashed()
 * @mixin Eloquent
 */
class Order extends Model implements BelongsToAdminInterface
{
    use Filterable;
    use SoftDeletes;
    use EloquentTentacle;
    use HasFactory;

    public const TABLE = 'orders';
    public const MORPH_NAME = 'order';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['dont_call_back'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payed' => 'bool',
        'dont_call_back' => 'bool'
    ];

    protected $attributes = [
        'payed' => false,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['payed_at'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'items.product',
        'status',
        'statusHistory',
        'user',
        'recipient',
        'client',
        'deliveryInformation',
        'paymentInformation',
        'delivery',
        'payment',
        'provider',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BelongsToAdminScope());
    }

    /**
     * @param Builder|Order $query
     */
    public function scopeNew(Builder $query): void
    {
        $query->whereStatusId(OrderStatus::NEW);
    }

    /**
     * @param $value
     */
    public function setPayedAttribute($value): void
    {
        $value = $this->castAttribute('payed', $value);

        if ($value !== $this->payed) {
            $this->attributes['payed_at'] = $value ? now() : null;
        }

        $this->attributes['payed'] = $value;
    }

    public function setPaid(string $mode): self
    {
        $this->payed = true;
        $this->payed_mode = $mode;

        /*$paidHistory = $this->statusHistory
            ->contains(function (OrderStatus $status) {
                return $status->isPaid();
            });*/

        if (!$this->hasStatusInHistory(OrderStatus::PAID)) {
            $this->changeStatus(OrderStatus::paidStatus());
        }

        return $this;
    }

    public function hasStatusInHistory(int $statusId): bool
    {
        return $this->statusHistory
            ->contains(fn (OrderStatus $status) => $status->id === $statusId);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function deliveryInformation(): HasOne
    {
        return $this->hasOne(OrderDeliveryInformation::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentInformation(): BelongsTo
    {
        return $this->belongsTo(OrderPaymentInformation::class, 'payment_information_id');
    }

    public function client(): HasOne
    {
        return $this->hasOne(OrderClient::class);
    }

    public function recipient(): HasOne
    {
        return $this->hasOne(OrderRecipient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function administrator(): HasOneThrough
    {
        return $this->hasOneThrough(
            Administrator::class,
            Provider::class,
            'id',
            'id',
            'provider_id',
            'admin_id',
        );
    }

    public function bonusHistory(): HasMany
    {
        return $this->hasMany(BonusHistory::class, 'order_id');
    }

    public function useBonusHistory(): HasMany
    {
        return $this->hasMany(BonusHistory::class, 'order_id')->useType();
    }

    public function getUseBonusHistory(): ?BonusHistory
    {
        return $this->useBonusHistory ? $this->useBonusHistory->first() : null;
    }

    public function usedBonuses(): int
    {
        return $this->getUseBonusHistory()->bonus ?? 0;
    }

    public function getBonusesSum(): int
    {
        $this->items->loadMissing('product');

        return $this->items
            ->sum(function (OrderItem $item) {
                return $item->getBonusesSum();
            });
    }

    public static function getCurrencyIsoCodeAttribute(): string
    {
        return 'KZT';
    }

    /**
     * @param OrderStatus|null $status
     * @return Order
     */
    public function changeStatus(?OrderStatus $status): Order
    {
        if ($status) {
            $this->status()->associate($status);

            if ($this->isDirty('status_id')) {
                $this->statusHistory()->attach($status->id);
            }
        }

        return $this;
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function isStatus(int $status): bool
    {
        return $this->status_id === $status;
    }

    public function statusHistory(): BelongsToMany
    {
        return $this->belongsToMany(OrderStatus::class, 'order_status_history', 'order_id', 'status_id')
            ->using(OrderStatusHistory::class)
            ->withPivot('created_at')
            ->orderBy('order_status_history.created_at');
    }

    public function getFullStatusHistory(): Collection
    {
        $history = $this->statusHistory->sortByDesc(function ($status) {
            return $status->pivot->created_at;
        });
        $latestStatus = $history->first();

        $found = !$latestStatus;

        foreach (OrderStatus::statusesSequence() as $status) {
            if ($found) {
                $history->prepend($status);
            } elseif ($status->id === $latestStatus->id) {
                $found = true;
            }
        }

        return $history;
    }

    public function deleteLatestHistory(): void
    {
        DB::table('order_status_history')
            ->where('order_id', $this->id)
            ->latest()
            ->limit(1)
            ->delete();
    }

    /**
     * @return string|null
     */
    public function getOnlinePaymentUrl(): ?string
    {
        if ($this->payed || round($this->whole_purchase_price, money()->precision()) == 0) {
            return null;
        }

        $payment = $this->payment;
        if (!$payment) {
            return null;
        }

        return Cache::driver('array')->rememberForever(
            __METHOD__,
            function () use ($payment) {
                $paymentDriver = $payment->makeDriver();
                if (!$paymentDriver || !$paymentDriver instanceof OnlinePaymentInterface) {
                    return null;
                }

                return $paymentDriver->redirectUrl(
                    $this,
                    route('thanks-page', $this->id),
                    route('payment-callback', [$this->id, $payment->driver])
                );
            }
        );
    }

    /**
     * @return string|null
     */
    public function getDeliveryAddressAttribute(): ?string
    {
        return optional(
            $this->delivery,
            function (Delivery $delivery) {
                return optional($delivery->makeDriver())->presentDeliveryAddress($this->deliveryInformation);
            }
        );
    }

    public function getWholePriceAttribute(): float
    {
        return $this->items->sum('whole_price');
    }

    public function getWholePurchasePriceAttribute(): float
    {
        return $this->items->sum('whole_purchase_price');
    }

    public function getTotalSum(): float
    {
        return $this->whole_purchase_price + $this->getDeliveryCost() - $this->getPaidSum();
    }

    public function getDeliveryCost(): float
    {
        return $this->deliveryInformation->delivery_cost ?? 0.0;
    }

    public function getQuantityAttribute(): string
    {
        return $this->items->count();
    }

    /**
     * @return mixed
     */
    public function getDiscountAttribute()
    {
        return $this->items->sum('discount');
    }

    public function getCustomer(): ClientData
    {
        if ($this->recipient->recipient_is_me) {
            return $this->client;
        }

        return $this->recipient;
    }

    public function createClient(): OrderClient
    {
        $client = $this->client()->make()->fillFromUser($this->user);

        $client->save();

        return $client;
    }

    public function getSumForPay(): float
    {
        return $this->payed
            ? 0.0
            : $this->getTotalSum();
    }

    public function getPaidSum(): float
    {
        $useBonusHistory = $this->getUseBonusHistory();

        return $useBonusHistory ? (float) $useBonusHistory->bonus : 0.0;
    }

    public function canBeReviewed(): bool
    {
        return $this->status->isDone();
    }

    public function canBeCancelled(): bool
    {
        return $this->status->isNew() || $this->status->isPaid();
    }

    public function getTotalWeight(): int
    {
        return $this->items
            ->reduce(function (int $sum, OrderItem $item) {
                return $sum + $item->quantity * $item->product->weight;
            }, 0);
    }
}
