<?php

namespace App\Models\Orders;

use App\Models\DiffableInterface;
use App\Models\Files\HasMedia;
use App\Models\Files\PaymentImage;
use App\Models\Files\Traits\HasMediaTrait;
use App\Traits\Diffable;
use App\Traits\Models\JoinColumnsTrait;
use Database\Factories\Orders\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property string terms
 * @property string driver_payment_uship_code
 * @property string driver_payment_comment
 * @property int driver_payment_method_id
 * @property int driver_payment_amount
 * @property int driver_payment_timestamp
 * @property int planned_date
 * @property int payment_deadline
 * @property string price
 * @property int method_id
 * @property int payment_days
 * @property string payment_type
 * @property bool driver_payment_data_sent
 * @property array old_values
 * @property int invoice_id
 * @property null|int invoice_issue_date
 * @property float|null $total_carrier_amount
 *
 * @property float $broker_payment_amount
 * @property int|null $broker_payment_planned_date
 * @property string|null $broker_payment_invoice_id
 * @property int|null $broker_payment_method_id
 * @property int|null $broker_payment_invoice_issue_date
 *
 * @property float $customer_payment_amount
 * @property int|null $customer_payment_planned_date
 * @property string|null $customer_payment_invoice_id
 * @property int|null $customer_payment_method_id
 * @property int|null $customer_payment_invoice_issue_date
 *
 * @property float|null $broker_fee_amount
 * @property int|null $broker_fee_planned_date
 * @property int|null $broker_fee_method_id
 *
 * @see Payment::order()
 * @property Order order
 *
 * @property mixed invoice_notes
 *
 * @method static PaymentFactory factory(...$parameters)
 */
class Payment extends Model implements HasMedia, DiffableInterface
{
    use HasMediaTrait;
    use Diffable;
    use JoinColumnsTrait;
    use HasFactory;

    public const PAYER_CUSTOMER = 'customer';
    public const PAYER_BROKER = 'broker';
    public const PAYER_CARRIER = 'carrier';
    public const PAYER_NONE = 'none';

    public const METHOD_COP = 1; // to remove in future
    public const METHOD_COD = 2; // to remove in future
    public const METHOD_COMCHECK = 3;
    public const METHOD_COMPANY_CHECK = 4;
    public const METHOD_ACH = 5;
    public const METHOD_TCH = 6;
    public const METHOD_USHIP = 7;
    public const METHOD_MONEY_ORDER = 8;
    public const METHOD_QUICKPAY = 9;
    public const METHOD_CASHAPP = 10;
    public const METHOD_PAYPAL = 11;
    public const METHOD_VENMO = 12;
    public const METHOD_ZELLE = 13;
    public const METHOD_CASH = 14;
    public const METHOD_CHECK = 15;
    public const METHOD_CERTIFIED_FUNDS = 16;

    public const METHOD_CREDIT_CARD = 17;

    public const CUSTOMER_METHODS = [
        self::METHOD_CASH => 'Cash',
        self::METHOD_CHECK => 'Check',
        self::METHOD_USHIP => 'Uship',
        self::METHOD_MONEY_ORDER => 'Money order',
        self::METHOD_QUICKPAY => 'QuickPay',
        self::METHOD_CASHAPP => 'Cashapp',
        self::METHOD_PAYPAL => 'Paypal',
        self::METHOD_VENMO => 'Venmo',
        self::METHOD_ZELLE => 'Zelle',
        self::METHOD_CREDIT_CARD => 'Credit card',
    ];

    public const BROKER_METHODS = [
        self::METHOD_CERTIFIED_FUNDS => 'Certified Funds',
        self::METHOD_COMCHECK => 'Comcheck',
        self::METHOD_ACH => 'ACH',
        self::METHOD_TCH => 'TCH',
        self::METHOD_CHECK => 'Check',
        self::METHOD_USHIP => 'Uship',
        self::METHOD_MONEY_ORDER => 'Money order',
        self::METHOD_QUICKPAY => 'QuickPay',
        self::METHOD_CASHAPP => 'Cashapp',
        self::METHOD_PAYPAL => 'Paypal',
        self::METHOD_VENMO => 'Venmo',
        self::METHOD_ZELLE => 'Zelle',
        self::METHOD_CREDIT_CARD => 'Credit card',
        self::METHOD_CASH => 'Cash',
    ];

    public const CARRIER_METHODS = [
        self::METHOD_CERTIFIED_FUNDS => 'Certified Funds',
        self::METHOD_CHECK => 'Check',
        self::METHOD_COMCHECK => 'Comcheck',
        self::METHOD_ACH => 'ACH',
        self::METHOD_TCH => 'TCH',
        self::METHOD_USHIP => 'Uship',
        self::METHOD_MONEY_ORDER => 'Money order',
        self::METHOD_QUICKPAY => 'QuickPay',
        self::METHOD_CASHAPP => 'Cashapp',
        self::METHOD_PAYPAL => 'Paypal',
        self::METHOD_VENMO => 'Venmo',
        self::METHOD_ZELLE => 'Zelle',
        self::METHOD_CREDIT_CARD => 'Credit card',
        self::METHOD_CASH => 'Cash',
    ];

    const PAYMENT_TERMS_BEGINS_ON = [
        'pickup' => 'Pickup',
        'delivery' => 'Delivery',
        'invoice-sent' => 'Day of invoice send',
    ];

    public const ALL_METHODS = self::CUSTOMER_METHODS + self::BROKER_METHODS + self::CARRIER_METHODS;

    public const BROKER_FEE_PAY_OVERDUE_STATUS = 10;
    public const BROKER_FEE_PAY_NOT_PAID_STATUS = 20;
    public const BROKER_FEE_PAY_PAID_STATUS = 30;

    public const BROKER_FEE_PAY_STATUSES = [
        self::BROKER_FEE_PAY_OVERDUE_STATUS => 'Overdue',
        self::BROKER_FEE_PAY_NOT_PAID_STATUS => 'Not paid',
        self::BROKER_FEE_PAY_PAID_STATUS => 'Paid'
    ];

    public const PAYMENT_DEADLINE_TIMEZONE = 'America/Chicago'; // CST

    public const TABLE_NAME = 'payments';

    protected $table = self::TABLE_NAME;
    protected $fillable = [
        'terms',

        'invoice_id',
        'invoice_notes',

        'total_carrier_amount',

        'customer_payment_amount',
        'customer_payment_method_id',
        'customer_payment_location',
        'customer_payment_planned_date',
        'customer_payment_invoice_id',
        'customer_payment_invoice_notes',
        'customer_payment_invoice_issue_date',

        'broker_payment_amount',
        'broker_payment_method_id',
        'broker_payment_days',
        'broker_payment_begins',
        'broker_payment_planned_date',
        'broker_payment_invoice_id',
        'broker_payment_invoice_notes',
        'broker_payment_invoice_issue_date',
        'broker_payment_planned_date',

        'broker_fee_amount',
        'broker_fee_method_id',
        'broker_fee_days',
        'broker_fee_begins',
        'broker_fee_planned_date',

        'driver_payment_data_sent',
        'driver_payment_amount',
        'driver_payment_uship_code',
        'driver_payment_comment',
        'driver_payment_method_id',
        'driver_payment_timestamp',
        'driver_payment_account_type',
    ];

    protected $casts = [
        'old_values' => 'array',
        'driver_payment_data_sent' => 'bool',
        'total_carrier_amount' => 'float',
        'broker_fee_amount' => 'float',
        'broker_payment_amount' => 'float',
        'customer_payment_amount' => 'float'
    ];

    public ?bool $isBrokerFeePaid;
    public ?bool $isCustomerPaid;
    public ?bool $isBrokerPaid;
    public bool $isPaid;
    public ?Carbon $paidAt = null;
    public ?Carbon $brokerFeePaidAt = null;
    public float $brokerAmountForecast;
    public float $customerAmountForecast;
    public float $brokerFeeAmountForecast;
    public float $totalCarrierAmount;

    public static function getMethodsList(): array
    {
        $methods = collect(self::ALL_METHODS);

        return $methods->map(
            function ($title, $id) {
                $availableFor = [];

                if (isset(self::CUSTOMER_METHODS[$id])) {
                    $availableFor[] = self::PAYER_CUSTOMER;
                }

                if (isset(self::BROKER_METHODS[$id])) {
                    $availableFor[] = self::PAYER_BROKER;
                }

                if (isset(self::CARRIER_METHODS[$id])) {
                    $availableFor[] = self::PAYER_CARRIER;
                }

                return (object)[
                    'id' => $id,
                    'title' => $title,
                    'available_for' => $availableFor,
                ];
            }
        )
            ->toArray();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id')
            ->withoutGlobalScopes();
    }

    public function getImageClass(): string
    {
        return PaymentImage::class;
    }

    public function resetDriverPaymentData(): void
    {
        $this->driver_payment_data_sent = false;
        $this->driver_payment_method_id = null;
        $this->driver_payment_amount = null;
        $this->driver_payment_uship_code = null;
        $this->driver_payment_comment = null;
        $this->driver_payment_timestamp = null;
        $this->clearMediaCollection(Order::DRIVER_PAYMENT_COLLECTION_NAME);

        $this->save();
    }

    public function isUSHIP(): bool
    {
        return $this->method_id === self::METHOD_USHIP;
    }

    public function isComCheck(): bool
    {
        return $this->method_id === self::METHOD_COMCHECK;
    }

    public function isCompanyCheck(): bool
    {
        return $this->method_id === self::METHOD_COMPANY_CHECK;
    }

    public function isPaypal(): bool
    {
        return $this->method_id === self::METHOD_PAYPAL;
    }

    public function isCashApp(): bool
    {
        return $this->method_id === self::METHOD_CASHAPP;
    }

    public function isVenmo(): bool
    {
        return $this->method_id === self::METHOD_VENMO;
    }

    public function isACH(): bool
    {
        return $this->method_id === self::METHOD_ACH;
    }

    public function isTCH(): bool
    {
        return $this->method_id === self::METHOD_TCH;
    }

    public function isMoneyOrder(): bool
    {
        return $this->method_id === self::METHOD_MONEY_ORDER;
    }

    public function isQuickPay(): bool
    {
        return $this->method_id === self::METHOD_QUICKPAY;
    }

    public function paidFlags(): self
    {
        if (isset($this->isCustomerPaid) && isset($this->isBrokerPaid)) {
            return $this;
        }
        $forecast = $this->getAmountsForecast();
        $actual = $this->getAmountsActual();

        $this->isCustomerPaid = empty($forecast[self::PAYER_CUSTOMER]) ? null : ($forecast[self::PAYER_CUSTOMER] <= $actual[self::PAYER_CUSTOMER]);
        $this->isBrokerPaid = empty($forecast[self::PAYER_BROKER]) ? null : ($forecast[self::PAYER_BROKER] <= $actual[self::PAYER_BROKER]);
        $this->isBrokerFeePaid = empty($forecast[self::PAYER_CARRIER]) ? null : ($forecast[self::PAYER_CARRIER] <= $actual[self::PAYER_CARRIER]);
        $this->customerAmountForecast = $this->isCustomerPaid ? 0.0 : $forecast[self::PAYER_CUSTOMER];
        $this->brokerAmountForecast = $this->isBrokerPaid ? 0.0 : $forecast[self::PAYER_BROKER];
        $this->brokerFeeAmountForecast = $this->isBrokerFeePaid ? 0.0 : $forecast[self::PAYER_CARRIER];
        $this->totalCarrierAmount = ($forecast[self::PAYER_BROKER] + $forecast[self::PAYER_CUSTOMER]) - $forecast[self::PAYER_CARRIER];
        $this->isPaid = ($this->isCustomerPaid === null || $this->isCustomerPaid === true) && ($this->isBrokerPaid === null || $this->isBrokerPaid === true);
        if ($this->isCustomerPaid === false || $this->isBrokerPaid === false) {
            $this->paidAt = null;
        }
        return $this;
    }

    private function getAmountsForecast(): array
    {
        $result = [
            self::PAYER_CUSTOMER => 0,
            self::PAYER_BROKER => 0,
            self::PAYER_CARRIER => 0,
        ];
        $this
            ->order
            ->expenses
            ->each(
                static function (Expense $expense) use (&$result): void {
                    if (empty($expense->to) || $expense->to === self::PAYER_NONE) {
                        return;
                    }
                    $result[$expense->to] += $expense->price;
                }
            );
        $this
            ->order
            ->bonuses
            ->each(
                static function (Bonus $bonus) use (&$result): void {
                    if (empty($bonus->to) || $bonus->to === self::PAYER_NONE) {
                        return;
                    }
                    $result[$bonus->to] += $bonus->price;
                }
            );
        $result[self::PAYER_CARRIER] += $this->broker_fee_amount ?? 0;
        $result[self::PAYER_BROKER] += $this->broker_payment_amount ?? 0;
        $result[self::PAYER_CUSTOMER] += $this->customer_payment_amount ?? 0;
        return $result;
    }

    private function getAmountsActual(): array
    {
        $result = [
            self::PAYER_CUSTOMER => 0,
            self::PAYER_BROKER => 0,
            self::PAYER_CARRIER => 0,
        ];
        $this
            ->order
            ->paymentStages
            ->each(
                function (PaymentStage $paymentStage) use (&$result): void {
                    $result[$paymentStage->payer] += $paymentStage->amount;
                    $date = Carbon::createFromTimestamp($paymentStage->payment_date);
                    if ($paymentStage->payer === self::PAYER_CARRIER && (!isset($this->brokerFeePaidAt) || $this->brokerFeePaidAt->lessThan($date))) {
                        $this->brokerFeePaidAt = $date;
                        return;
                    }
                    if (!isset($this->paidAt) || $this->paidAt->lessThan($date)) {
                        $this->paidAt = $date;
                    }
                }
            );
        return $result;
    }
}
