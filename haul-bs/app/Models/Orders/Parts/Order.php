<?php

namespace App\Models\Orders\Parts;

use App\Casts\Order\Parts\EcommerceClientCast;
use App\Contracts\Orders\Orderable;
use App\Contracts\Payment\PaymentDriverInterface;
use App\Drivers\PaypalDriver;
use App\Drivers\StripeDriver;
use App\Entities\Order\Parts\EcommerceClientEntity;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Enums\Orders\Parts\ShippingMethod;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Foundations\Casts\Locations\AddressCast;
use App\Foundations\Entities\Locations\AddressEntity;
use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Contracts\HasComments;
use App\Foundations\Modules\Comment\Traits\InteractsWithComment;
use App\Foundations\Modules\History\Contracts\HasHistory;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Foundations\Modules\History\Traits\InteractsWithHistory;
use App\ModelFilters\Orders\Parts\OrderFilter;
use App\Models\Customers\Customer;
use App\Models\Inventories\Inventory;
use App\Models\Users\User;
use App\Traits\Orders\OrderableTraits;
use App\Traits\Orders\Parts\CanActionScope;
use Carbon\CarbonImmutable;
use Database\Factories\Orders\Parts\OrderFactory;
use Eloquent;
use App\Foundations\Traits\Filters\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string order_number
 * @property int customer_id
 * @property int sales_manager_id
 * @property OrderStatus status
 * @property OrderStatus|null status_before_deleting
 * @property Carbon|null status_changed_at
 * @property PaymentMethod payment_method
 * @property PaymentTerms payment_terms
 * @property bool with_tax_exemption
 * @property AddressEntity delivery_address
 * @property AddressEntity billing_address
 * @property bool|null is_paid
 * @property Carbon|null paid_at
 * @property Carbon|null refunded_at   // товары заказ были возвращены
 * @property float|null total_amount
 * @property float|null paid_amount
 * @property float|null debt_amount
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property OrderSource source
 * @property DeliveryType|null delivery_type
 * @property Carbon|null draft_at           // если проставлена дата, значит заявка в состоянии черновика
 * @property Carbon|null delivered_at       // дата переведения в статус delivered
 * @property Carbon|null past_due_at        // дата когда наступила просрочка
 * @property EcommerceClientEntity ecommerce_client        // данные по клиенту для заказа с ecomm , если он не авторизован
 * @property string|null ecommerce_client_email  // email клиенту для заказа с ecomm , для фильтрации
 * @property string|null ecommerce_client_name   // имя по клиенту для заказа с ecomm , для фильтрации
 * @property float delivery_cost
 *
 * @see self::items()
 * @property Item[]|HasMany items
 *
 * @see self::deliveries()
 * @property Delivery[]|HasMany deliveries
 *
 * @see self::inventories()
 * @property Inventory[]|HasManyThrough inventories
 *
 * @see self::shippingMethods()
 * @property Shipping[]|HasMany shippingMethods
 *
 * @see self::customer()
 * @property Customer|BelongsTo customer
 *
 * @see self::salesManager()
 * @property User|BelongsTo salesManager
 *
 * @see self::payments()
 * @property Payment[]|HasMany payments
 *
 * @mixin Eloquent
 * @method static Builder|self query()
 * @method static OrderFactory factory(...$parameters)
 */
class Order extends BaseModel implements
    HasComments,
    HasHistory,
    Orderable
{
    use Filterable;
    use HasFactory;
    use SoftDeletes;
    use InteractsWithComment;
    use InteractsWithHistory;
    use OrderableTraits;
    // self
    use CanActionScope;

    public const TABLE = 'parts_orders';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'parts-order';
    public const DEFAULT_STATUS = OrderStatus::New;

    /** @var array<int, string> */
    protected $fillable = [];

    protected static array $drivers = [];

    protected PaymentDriverInterface|null $driverInstance = null;

    /** @var array<string, string> */
    protected $casts = [
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'draft_at' => 'datetime',
        'delivered_at' => 'datetime',
        'past_due_at' => 'datetime',
        'status' => OrderStatus::class,
        'status_before_deleting' => OrderStatus::class,
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'debt_amount' => 'float',
        'delivery_cost' => 'float',
        'delivery_address' => AddressCast::class,
        'billing_address' => AddressCast::class,
        'with_tax_exemption' => 'boolean',
        'payment_method' => PaymentMethod::class,
        'payment_terms' => PaymentTerms::class,
        'source' => OrderSource::class,
        'delivery_type' => DeliveryType::class,
        'ecommerce_client' => EcommerceClientCast::class,
    ];

    public function modelFilter()
    {
        return $this->provideFilter(OrderFilter::class);
    }

    public static function assertSalesManager(Order $order): void
    {
        if(
            auth_user()->role->isSalesManager()
            && $order->sales_manager_id
            && $order->sales_manager_id !== auth_user()->id
        ){
            throw new \Exception(__("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
        }
    }

    public function isPartsOrder(): bool
    {
        return true;
    }

    public function isPaid(): bool
    {
        return $this->is_paid;
    }

    public function isRefunded(): bool
    {
        return !is_null($this->refunded_at);
    }

    public function isDraft(): bool
    {
        return !is_null($this->draft_at);
    }

    public function hasFreeShippingInventory(): bool
    {
        return $this->items->contains('free_shipping', true);
    }

    public function hasPaidShippingInventory(): bool
    {
        return $this->items->contains('free_shipping', false);
    }

    public function hasOverloadInventory(): bool
    {
        foreach ($this->items as $item) {
            if($item->isOverload()) return true;
        }

        return false;
    }

    public function isDeliveryMethodPickup(): bool
    {
        return isset($this->shipping_method['name']) && $this->shipping_method['name'] == ShippingMethod::Pickup();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_manager_id')
            ->withTrashed();
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'order_id');
    }

    public function itemsFreeShipping(): Collection
    {
        return $this->items->where('free_shipping', true);
    }

    public function itemsPaidShipping(): Collection
    {
        return $this->items->where('free_shipping', false);
    }

    public function shippingMethods(): HasMany
    {
        return $this->hasMany(Shipping::class, 'order_id');
    }

    public function inventories(): HasManyThrough
    {
        return $this->hasManyThrough(
            Inventory::class,
            Item::class,
            'order_id',
            'id',
            'id',
            'inventory_id'
        );
    }

    public function dataForUpdateHistory(): array
    {
        $old = $this->getAttributes();

        return $old;
    }

    public function setAmounts(bool $save = true): void
    {
        $this->refresh();

        $this->total_amount = $this->getAmount();
        $this->paid_amount = $this->getPaymentsAmount();
        $this->debt_amount = $this->total_amount - $this->paid_amount;

        if ($save) $this->save();
    }

    public function getTotalOnlyItems(): float
    {
        $total = 0;

        foreach ($this->items as $item) {
            /** @var $item Item */
            $total += $item->total();
        }

        return round($total, 2);
    }

    public function isTotalMore99(float|null $total = null): bool
    {
        if(is_null($total)) $total = $this->getTotalOnlyItems();

        return $total > config('shipping.min_cost_for_free_delivery') || $this->delivery_type?->isPickup();
    }


    public function getTotalDelivery(float|null $total = null): float
    {
        if(is_null($total)) $total = $this->getTotalOnlyItems();

        if ($total > config('shipping.min_cost_for_free_delivery') || $this->delivery_type?->isPickup()) {
            return $this->delivery_cost;
        }

        $totalDelivery = 0;
        foreach ($this->items as $item) {
            /** @var $item Item */

            $totalDelivery += price_with_discount($item->delivery_cost, $item->discount) * $item->qty;
        }

        return $totalDelivery + $this->delivery_cost;
    }

    // тотал товаров в заказе, по старой цене
    public function getSubTotalOnlyItems(): float
    {
        $total = 0;

        foreach ($this->items as $item) {
            /** @var $item Item */
            $total += $item->subtotal();
        }

        return $total;
    }

    public function getAmount(): float
    {
        $inventoryAmount = $this->getTotalOnlyItems();
        $deliveryAmount = $this->getTotalDelivery($inventoryAmount);
        // если не выбран тип доставка и тотал меньше 99$, не добавляем второй delivery_cost(он же shipping)
        if(
            is_null($this->delivery_type)
            && $this->total_amount
            && $this->total_amount < config('shipping.min_cost_for_free_delivery')
        ) {
            $deliveryAmount -= $this->getItemsDeliveryCost();
        }
        $taxAmount = $this->getTax();

        return round($inventoryAmount + $taxAmount + $deliveryAmount, 2);
    }

    public function getSubtotal(): float
    {
        return round($this->getSubTotalOnlyItems(), 2);
    }

    public function getPaymentsAmount(): float
    {
        $amount = 0;
        foreach ($this->payments as $payment) {
            $amount += $payment->amount;
        }

        return $amount;
    }

    public function getItemsDeliveryCost(): float
    {
        $amount = 0;
        foreach ($this->items as $item) {
            $amount += ($item->delivery_cost * $item->qty);
        }

        return $amount;
    }

    public function getTax(): float
    {
        $tax = 0;
        if(
            $this->billing_address?->isIllinois()
            && !$this->with_tax_exemption
        ){
            $itemsTotal = $this->getTotalOnlyItems();
            $percent = config('orders.parts.tax.illinois');

            $tax = (float)number_format(percentage($itemsTotal, $percent), 2);
        }

        return $tax;
    }

    public function getSavingAmount(): float
    {
        $totalMore = $this->isTotalMore99();

        $amount = 0;
        foreach ($this->items as $item) {
            $amount += $item->getSaving($totalMore);
        }

        return round($amount, 2);
    }

    public function resolvePaidStatus(): void
    {
        if ($this->total_amount <= $this->paid_amount && !$this->is_paid) {
            $this->setIsPaid();

        } elseif ($this->total_amount > $this->paid_amount && $this->is_paid) {
            $this->setIsNotPaid();
        }
    }

    public function setIsPaid()
    {
        $this->is_paid = true;
        $this->paid_at = now();
        $this->save();

        event(new RequestToEcom($this, OrderPartsHistoryService::ACTION_IS_PAID));
    }

    public function setIsNotPaid()
    {
        $this->is_paid = false;
        $this->paid_at = null;
        $this->save();

        event(new RequestToEcom($this, OrderPartsHistoryService::ACTION_IS_PAID));
    }

    public function isOverdue(): bool
    {
        return !$this->is_paid
            && $this->past_due_at
            && CarbonImmutable::now() > $this->past_due_at;
    }
    public function getOverdueDays(): null|int
    {
        if (!$this->isOverdue()) return null;

        return CarbonImmutable::now()->diffInDays($this->past_due_at);
    }

    public function makeDriver(): PaymentDriverInterface|null
    {
        $paymentMethod = $this->payment_method->value;
        if ($this->driverInstance === null) {
            $drivers = [
                PaymentMethod::PayPal->value => PaypalDriver::class,
                PaymentMethod::Online->value => StripeDriver::class
            ];

            if (!$this->payment_method) {
                return null;
            }

            if (!array_key_exists($paymentMethod, $drivers)) {
                return null;
            }

            $driver = $drivers[$paymentMethod];

            $this->driverInstance = class_exists($driver) ? resolve($driver) : null;
        }

        return $this->driverInstance;
    }
}
