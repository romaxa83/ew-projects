<?php

namespace App\Models\Inventories;

use App\Enums\Inventories\Transaction\DescribeType;
use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Inventories\Transaction\PaymentMethod;
use App\Enums\Orders\OrderType;
use App\Foundations\Casts\Contact\EmailCast;
use App\Foundations\Casts\Contact\PhoneCast;
use App\Foundations\Models\BaseModel;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\ModelFilters\Inventories\TransactionFilter;
use Database\Factories\Inventories\TransactionFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int inventory_id
 * @property OrderType|null order_type
 * @property int|null order_id
 * @property int|null order_parts_id
 * @property OperationType operation_type
 * @property string|null invoice_number
 * @property float|null price
 * @property float quantity
 * @property DescribeType|null describe
 * @property Carbon transaction_date
 * @property bool is_reserve
 * @property float|null discount
 * @property float|null tax
 * @property Carbon|null payment_date
 * @property PaymentMethod|null payment_method
 * @property string|null first_name
 * @property string|null last_name
 * @property Phone|null phone
 * @property Email|null email
 * @property string|null company_name
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property int|null origin_id
 *
 * @see self::inventory()
 * @property Inventory $inventory
 *
 * @see self::scopeSelectPriceWithTaxAndDiscount()
 * @method static Transaction|Builder selectPriceWithTaxAndDiscount()
 *
 * @mixin Eloquent
 *
 * @method static TransactionFactory factory(...$parameters)
 */
class Transaction extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'inventory_transactions';
    protected $table = self::TABLE;

    /**@var array<int, string> */
    protected $fillable = [
        'order_id',
        'order_type',
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

    /** @var array<string, string> */
    protected $casts = [
        'is_reserve' => 'boolean',
        'transaction_date' => 'date',
        'payment_date' => 'date',
        'operation_type' => OperationType::class,
        'describe' => DescribeType::class,
        'payment_method' => PaymentMethod::class,
        'price' => 'double',
        'quantity' => 'double',
        'phone' => PhoneCast::class,
        'email' => EmailCast::class,
        'order_type' => OrderType::class,
    ];

    public function modelFilter()
    {
        return $this->provideFilter(TransactionFilter::class);
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
//
//    public static function getPaymentMethodsList(): array
//    {
//        $data = [];
//        foreach (self::PAYMENT_METHODS as $key => $title) {
//            $data[] = [
//                'key' => $key,
//                'title' => $title,
//            ];
//        }
//
//        return $data;
//    }
}

