<?php

namespace App\Models\BodyShop\Inventories;

use App\ModelFilters\BodyShop\Inventories\TransactionFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Inventories
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $operation_type
 * @property string $invoice_number
 * @property float|null $price
 * @property float $quantity
 * @property int $inventory_id
 * @property int|null $order_id
 * @property string|null $describe
 * @property Carbon $transaction_date
 * @property bool $is_reserve
 * @property float|null $discount
 * @property float|null $tax
 * @property Carbon|null $payment_date
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $company_name
 * @property string|null $payment_method
 *
 * @see Transaction::inventory()
 * @property Inventory $inventory
 *
 * @see Transaction::scopeSelectPriceWithTaxAndDiscount()
 * @method static Builder|Transaction selectPriceWithTaxAndDiscount()
 *
 * @mixin Eloquent
 */
class Transaction extends Model
{
    use Filterable;

    public const TABLE_NAME = 'bs_inventory_transactions';

    public const OPERATION_TYPE_PURCHASE = 'purchase';
    public const OPERATION_TYPE_SOLD = 'sold';

    public const DESCRIBE_SOLD = 'sold';
    public const DESCRIBE_DEFECT = 'defect';
    public const DESCRIBE_BROKE = 'broke';

    public const PAYMENT_METHOD_CASH = 'cash';
    public const PAYMENT_METHOD_CHECK = 'check';
    public const PAYMENT_METHOD_MONEY_ORDER = 'money_order';
    public const PAYMENT_METHOD_QUICK_PAY = 'quick_pay';
    public const PAYMENT_METHOD_PAYPAL = 'paypal';
    public const PAYMENT_METHOD_CASHAPP = 'cashapp';
    public const PAYMENT_METHOD_VENMO = 'venmo';
    public const PAYMENT_METHOD_ZELLE = 'zelle';
    public const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_WIRE_TRANSFER = 'wire_transfer';

    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_CASH => 'Cash',
        self::PAYMENT_METHOD_CHECK => 'Check',
        self::PAYMENT_METHOD_MONEY_ORDER => 'Money Order',
        self::PAYMENT_METHOD_QUICK_PAY => 'Quick Pay',
        self::PAYMENT_METHOD_PAYPAL => 'PayPal',
        self::PAYMENT_METHOD_CASHAPP => 'Cashapp',
        self::PAYMENT_METHOD_VENMO => 'Venmo',
        self::PAYMENT_METHOD_ZELLE => 'Zelle',
        self::PAYMENT_METHOD_CREDIT_CARD => 'Credit Card',
        self::PAYMENT_METHOD_CARD => 'Card',
        self::PAYMENT_METHOD_WIRE_TRANSFER => 'Wire transfer',
    ];


    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'order_id',
        'inventory_id',
        'operation_type',
        'quantity',
        'transaction_date',
        'invoice_number',
        'describe',
        'price',
        'discount',
        'tax',
        'payment_date',
        'first_name',
        'last_name',
        'phone',
        'email',
        'is_reserve',
        'company_name',
        'payment_method',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'payment_date' => 'date',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(TransactionFilter::class);
    }

    public function isPurchase(): bool
    {
        return $this->operation_type === self::OPERATION_TYPE_PURCHASE;
    }

    public function isSold(): bool
    {
        return $this->operation_type === self::OPERATION_TYPE_SOLD;
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class)->withTrashed();
    }

    public function getTotalAmount(): float
    {
        $totalAmount = $this->price * $this->quantity;

        if ($this->discount) {
            $discount = $totalAmount * $this->discount / 100;
            $totalAmount -= $discount;
        }

        if ($this->tax) {
            $tax = $totalAmount * $this->tax / 100;
            $totalAmount += $tax;
        }

        return round($totalAmount, 2);
    }

    public function scopeSelectPriceWithTaxAndDiscount(Builder $query): Builder
    {
        return $query->selectRaw('ROUND(price - (price * coalesce(discount, 0) / 100) + ((price - (price *  coalesce(discount, 0) / 100)) *  coalesce(tax, 0) / 100), 2) as price_with_discount_and_tax');
    }

    public static function getPaymentMethodsList(): array
    {
        $data = [];
        foreach (self::PAYMENT_METHODS as $key => $title) {
            $data[] = [
                'key' => $key,
                'title' => $title,
            ];
        }

        return $data;
    }
}
