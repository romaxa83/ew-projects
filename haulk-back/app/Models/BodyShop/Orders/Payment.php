<?php

namespace App\Models\BodyShop\Orders;

use App\Collections\Models\BodyShop\Orders\PaymentsCollection;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Orders\TypeOfWork
 *
 * @property int $id
 * @property float $amount
 * @property Carbon $payment_date
 * @property string $payment_method
 * @property string $notes
 * @property int $order_id
 * @property string|null $reference_number
 *
 * @mixin Eloquent
 */
class Payment extends Model implements DiffableInterface
{
    use Filterable;
    use Diffable;

    public const TABLE_NAME = 'bs_order_payments';

    public const PAYMENT_METHOD_CASH = 'cash';
    public const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    public const PAYMENT_METHOD_MONEY_ORDER = 'money_order';
    public const PAYMENT_METHOD_QUICK_PAY = 'quick_pay';
    public const PAYMENT_METHOD_CASHAPP = 'cashapp';
    public const PAYMENT_METHOD_PAYPAL = 'paypal';
    public const PAYMENT_METHOD_VENMO = 'venmo';
    public const PAYMENT_METHOD_ZELLE = 'zelle';

    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_WIRE_TRANSFER = 'wire_transfer';

    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_CASH => 'Cash',
        self::PAYMENT_METHOD_CREDIT_CARD => 'Credit Card',
        self::PAYMENT_METHOD_MONEY_ORDER => 'Money Order',
        self::PAYMENT_METHOD_QUICK_PAY => 'Quick Pay',
        self::PAYMENT_METHOD_CASHAPP => 'Cashapp',
        self::PAYMENT_METHOD_PAYPAL => 'PayPal',
        self::PAYMENT_METHOD_VENMO => 'Venmo',
        self::PAYMENT_METHOD_ZELLE => 'Zelle',
        self::PAYMENT_METHOD_CARD => 'Card',
        self::PAYMENT_METHOD_WIRE_TRANSFER => 'Wire transfer',
    ];

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'order_id',
        'reference_number',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public $timestamps = false;

    public function newCollection(array $models = []): PaymentsCollection
    {
        return PaymentsCollection::make($models);
    }

    public static function getMethodsList(): array
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
