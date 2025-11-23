<?php

namespace App\Models\Order\Payroll;

use App\Enums\Common\DateFormat;
use App\Helpers\DbConnections;
use App\ModelFilters\Orders\PayrollFilter;
use App\Models\CashRegistry\CashRegistry;
use App\Models\CashRegistry\CashRegistryItem;
use App\Models\Employee;
use App\Models\Order;
use App\Models\User\Role;
use App\Services\CashRegistry\CashRegistryService;
use App\User;
use Carbon\CarbonImmutable;
use Database\Factories\Orders\Payroll\PayrollFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property positive-int id
 * @property positive-int order_id
 * @property positive-int|null processed_employee_id
 * @property positive-int|null creator_id
 * @property float hours
 * @property array paid_form_bol
 * @property boolean is_processed
 * @property float|null cash_on_hand
 * @property CarbonImmutable|null start_at
 * @property CarbonImmutable|null end_at
 * @property Carbon|null processed_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see self::processedEmployee()
 * @property Employee|BelongsTo processedEmployee
 *
 * @see self::creator()
 * @property Employee|BelongsTo creator
 *
 * @see self::order()
 * @property Order|BelongsTo order
 *
 * @see self::items()
 * @property Item[]|BelongsTo items
 *
 * @mixin \Eloquent
 * @method static PayrollFactory factory(...$parameters)
 */
class Payroll extends Model implements Auditable
{
    use HasFactory;
    use Filterable;
    use AuditableTrait;

    public const MORPH_NAME = 'order-payroll';

    public const TABLE = 'order_payrolls';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'order_id',
    ];

    protected $dates = [
        'processed_at',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'paid_form_bol' => 'array',
        'is_processed' => 'boolean',
        'hours' => 'float',
        'sum' => 'float',
        'cash_on_hand' => 'float',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(PayrollFilter::class);
    }

    protected static function newFactory(): PayrollFactory
    {
        return PayrollFactory::new();
    }

    public function processedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'processed_employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'creator_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function getPaidFromBol(): array
    {
        $data = $this->paid_form_bol;
        if(is_string($data)){
            $data = json_to_array($data);
        }

        return [
            'cash' => isset($data['cash']) && !empty($data['cash']) ? $data['cash'] : 0,
            'zelle' => isset($data['zelle']) && !empty($data['zelle']) ? $data['zelle'] : 0,
            'credit_card' => isset($data['credit_card_clean']) && !empty($data['credit_card_clean']) ? $data['credit_card_clean'] : 0,
            'credit_card_fee' => isset($data['credit_card_fee']) && !empty($data['credit_card_fee']) ? $data['credit_card_fee'] : 0,
        ];
    }

    public function getSumCashPaid(): float
    {
        $result = 0;
        foreach ($this->items as $item) {
            $result += $item->getCashPaid();
        }

        return round($result, 2);
    }

    public function getSumCCPaid(): float
    {
        $result = 0;
        foreach ($this->items as $item) {
            $result += $item->getCCPaid();
        }

        return round($result, 2);
    }

    public function getMargin(): float
    {
        $sum = $this->getSumCashPaid() + $this->getSumCCPaid();
        $paidData = $this->getPaidFromBol();
        $paid = $paidData['cash'] + $paidData['zelle'] + $paidData['credit_card'] + $paidData['credit_card_fee'];

        if($paid === 0) return 0;

        return round($sum/$paid, 2);
    }

    public function getMarginCash(): float
    {
        $paidData = $this->getPaidFromBol();
        $paid = $paidData['cash'] + $paidData['zelle'] + $paidData['credit_card'] + $paidData['credit_card_fee'];

        if($paid === 0) return 0;

        return round($paid * $this->getMargin(), 2);
    }

    public function getActionMeta(): array
    {
        $canView = false;
        $canEdit = false;
        $canSwitch = false;
        $roles = [];

        if(!is_null(auth_user())){
            /** @var $user User */
            $user = auth_user();
            if($user->isAccountant()){
                $canView = true;
                $canEdit = true;
                $canSwitch = true;

                if($this->is_processed){
                    $canEdit = false;
                }
            }

            if($canEdit){
                $roles = Role::query()
                    ->where('for_crew', true)
                    ->get()
                    ->pluck('title', 'id')
                    ->toArray();
            }
        }

        return [
            'actions' => [
                'can_view' => $canView,
                'can_edit' => $canEdit,
                'can_switch' => $canSwitch,
            ],
            'roles' => $roles
        ];
    }

    public function calcCashOnHandsResult(): float
    {
        // Cash Collected - SUM CASH PAID + PREVIOUS BALANCE
        logger_info('CALC [CashOnHands]');

        // get previous balance
        $from = $this->created_at->setTimezone(DateFormat::TZ_CHICAGO())
            ->startOfDay();

        $cashRegistry = CashRegistry::query()
            ->where('employee_id', $this->creator_id)
            ->first();

        $itemsPrevious = CashRegistryItem::query()
            ->where('cash_registry_id', $cashRegistry->id)
            ->where('insert_date', '<', $from)
            ->orderBy('insert_date')
            ->get();
        $cashRegistryService = resolve(CashRegistryService::class);
        $previousBalance = $cashRegistryService->getCashOnHand($itemsPrevious);

        // Cash Collected
        $paidData = $this->getPaidFromBol();
        $cashCollected = $paidData['cash'];

        $sumCashPaid = $this->getSumCashPaid();

        $result = round(
            $cashCollected - $sumCashPaid + $previousBalance
            ,2
        );

        logger_info('CALC [CashOnHands] DATA', [
            'from' => $from,
            'employee_id' => $this->creator_id,
            'cash_registry_id' => $cashRegistry->id,
            'previousBalance' => $previousBalance,
            'cashCollected' => $cashCollected,
            'sumCashPaid' => $sumCashPaid,
            'result' => $result,
        ]);

        return $result;
    }
}
