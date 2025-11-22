<?php

namespace App\Models\Orders\BS;

use App\Contracts\Orders\Orderable;
use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\BS\OrderPaymentStatus;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Contracts\HasComments;
use App\Foundations\Modules\Comment\Traits\InteractsWithComment;
use App\Foundations\Modules\History\Contracts\HasHistory;
use App\Foundations\Modules\History\Traits\InteractsWithHistory;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\OrderImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\ModelFilters\Orders\BS\OrderFilter;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use App\Traits\Orders\OrderableTraits;
use Database\Factories\Orders\BS\OrderFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null vehicle_id
 * @property int|null vehicle_type
 * @property float|null discount
 * @property Carbon implementation_date
 * @property int mechanic_id
 * @property string notes
 * @property OrderStatus status
 * @property string order_number
 * @property float tax_inventory
 * @property float tax_labor
 * @property Carbon due_date
 * @property Carbon status_changed_at
 * @property OrderStatus|null status_before_deleting
 * @property bool|null is_billed
 * @property Carbon|null billed_at
 * @property bool|null is_paid
 * @property Carbon|null paid_at
 * @property float|null total_amount
 * @property float|null paid_amount
 * @property float|null debt_amount
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property int|null origin_id
 * @property float|null profit
 * @property float|null parts_cost
 *
 * @see self::typesOfWork()
 * @property TypeOfWork[] $typesOfWork
 *
 * @see self::mechanic()
 * @property User|BelongsTo $mechanic
 *
 * @see self::vehicle()
 * @property Vehicle|MorphTo $vehicle
 *
 * @see self::inventories()
 * @property TypeOfWorkInventory[] $inventories
 *
 * @see self::payments()
 * @property Payment[] $payments
 *
 * @see Order::scopeOrderByDefault()
 * @method static Builder|Order orderByDefault()
 *
 * @see Order::scopeOrderForReport()
 * @method static Builder|Order orderForReport()
 *
 * @see Order::scopeOpen()
 * @method static Builder|Order open()
 *
 * @see Order::scopeClosed()
 * @method static Builder|Order closed()
 *
 * @mixin Eloquent
 *
 * @method static OrderFactory factory(...$parameters)
 */
class Order extends BaseModel implements
    HasComments,
    HasMedia,
    HasHistory,
    Orderable
{
    use Filterable;
    use HasFactory;
    use SoftDeletes;
    use InteractsWithComment;
    use InteractsWithMedia;
    use InteractsWithHistory;
    use OrderableTraits;

    public const TABLE = 'bs_orders';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'bs-order';

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    /**
     * @var array
     */
    protected $fillable = [
        'truck_id',
        'trailer_id',
        'discount',
        'implementation_date',
        'mechanic_id',
        'notes',
        'status',
        'order_number',
        'tax_labor',
        'tax_inventory',
        'payment_status',
        'status',
        'due_date',
        'status_changed_at',
        'profit',
        'parts_cost',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'implementation_date' => 'datetime',
        'due_date' => 'datetime',
        'status_changed_at' => 'datetime',
        'billed_at' => 'datetime',
        'paid_at' => 'datetime',
        'status' => OrderStatus::class,
        'status_before_deleting' => OrderStatus::class,
        'discount' => 'float',
        'tax_inventory' => 'float',
        'tax_labor' => 'float',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(OrderFilter::class);
    }

    public function isPartsOrder(): bool
    {
        return false;
    }

    public function typesOfWork(): HasMany
    {
        return $this->hasMany(TypeOfWork::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function vehicle(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id')
            ->withTrashed();
    }

    public function dataForUpdateHistory(): array
    {
        $old = $this->getAttributes();
        $old['media'] = $this->media()->get();
        $old['type_of_work'] = $this->typesOfWork()->with('inventories')->get();
        $old['type_of_work_hash'] = hash_data($this->typesOfWork()->get());

        return $old;
    }

    public function getAmount(): float
    {
        $inventoryAmount = 0;
        $laborAmount = 0;

        foreach ($this->typesOfWork as $work) {
            /** @var $work TypeOfWork */
            $inventoryAmount += $work->getInventoryAmount();
            $laborAmount += $work->getLaborAmount();
        }

        $inventoryDiscountAmount = $this->discount
            ? round($inventoryAmount * $this->discount / 100, 2)
            : 0
        ;
        $inventoryAmount -= $inventoryDiscountAmount;
        $inventoryTaxAmount = $this->tax_inventory
            ? round($inventoryAmount * $this->tax_inventory / 100, 2)
            : 0
        ;
        $inventoryAmount += $inventoryTaxAmount;

        $laborDiscountAmount = $this->discount
            ? round($laborAmount * $this->discount / 100, 2)
            : 0;
        $laborAmount -= $laborDiscountAmount;
        $laborTaxAmount = $this->tax_labor
            ? round($laborAmount * $this->tax_labor / 100, 2)
            : 0
        ;
        $laborAmount += $laborTaxAmount;

        return round($inventoryAmount + $laborAmount, 2);
    }

    public function getInventoryAmount(): float
    {
        $inventoryAmount = 0;

        foreach ($this->typesOfWork as $work) {
            $inventoryAmount += $work->getInventoryAmount();
        }

        return $inventoryAmount;
    }

    public function getLaborAmount(): float
    {
        $laborAmount = 0;

        foreach ($this->typesOfWork as $work) {
            $laborAmount += $work->getLaborAmount();
        }

        return $laborAmount;
    }

    public function getImageClass(): string
    {
        return OrderImage::class;
    }

    public function getAttachments(): array
    {
        return $this
            ->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

    public function scopeOrderByDefault(Builder $builder): Builder
    {
        $statuses = '{' . implode(',', EnumHelper::values(OrderStatus::class)) . '}';
        return $builder
            ->addSelect(DB::raw('ARRAY_POSITION(\'' . $statuses . '\', status) as status_sort'))
            ->orderBy('status_sort', 'asc')
            ->orderBy('implementation_date', 'asc');
    }

    public function inventories(): HasManyThrough
    {
        return $this->hasManyThrough(
            TypeOfWorkInventory::class,
            TypeOfWork::class,
            'order_id',
            'type_of_work_id',
            'id',
            'id'
        );
    }

    public function isPricesChanged(): bool
    {
        return $this->inventories()
            ->leftJoin(
                Inventory::TABLE,
                Inventory::TABLE . '.id', '=', TypeOfWorkInventory::TABLE . '.inventory_id')
            ->whereColumn(Inventory::TABLE . '.price_retail', '!=', TypeOfWorkInventory::TABLE . '.price')
            ->exists();
    }

    public function isStatusCanBeChanged(): bool
    {
        if (
            $this->status->isFinished()
            && now()->timestamp - $this->status_changed_at->timestamp > config('orders.bs.do_not_change_finished_status_after') * 60
        ) {
            return false;
        }

        return true;
    }

    public function isPaid(): bool
    {
        return $this->is_paid;
    }

    public function getPaymentsAmount(): float
    {
        $amount = 0;
        foreach ($this->payments as $payment) {
            $amount += $payment->amount;
        }

        return $amount;
    }

    public function getCurrentPaymentStatus(): null|string
    {
        if ($this->is_paid) return OrderPaymentStatus::Paid->value;
        if ($this->is_billed) return OrderPaymentStatus::Billed->value;

        return null;
    }

    public function isOverdue(): bool
    {
        return !$this->is_paid && now()->startOfDay() > $this->due_date;
    }

    public function markAsBilled(): void
    {
        $this->is_billed = true;
        $this->billed_at = now();
        $this->save();
    }

    public function resolvePaidStatus(): void
    {
        if ($this->total_amount <= $this->paid_amount && !$this->is_paid) {
            $this->is_paid = true;
            $this->paid_at = now();
            $this->save();

        } elseif ($this->total_amount > $this->paid_amount && $this->is_paid) {
            $this->is_paid = false;
            $this->paid_at = null;
            $this->save();
        }
    }

    public function markAsNotBilled(): void
    {
        $this->is_billed = false;
        $this->billed_at = null;
        $this->save();
    }

    public function setAmounts(): void
    {
        $this->refresh();
        $this->total_amount = $this->getAmount();
        $this->paid_amount = $this->getPaymentsAmount();
        $this->debt_amount = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function getCurrentDue(): ?float
    {
        return $this->due_date >= now()->startOfDay() ? $this->debt_amount : 0;
    }

    public function getPastDue(): ?float
    {
        return $this->due_date < now()->startOfDay() ? $this->debt_amount : 0;
    }

    public function scopeOrderForReport(
        Builder $builder,
        string $orderBy,
        string $orderDirection
    ): Builder
    {
        switch ($orderBy) {
            case 'total_due':
                $builder->orderByRaw('CASE WHEN debt_amount > 0 THEN debt_amount END ' . $orderDirection . ' NULLS LAST')
                    ->orderBy('id', 'desc');
                break;
            case 'current_due':
                $builder->orderByRaw('CASE WHEN due_date >= now() AND debt_amount > 0 THEN debt_amount END ' . $orderDirection . ' NULLS LAST')
                    ->orderBy('id', 'desc');
                break;
            case 'past_due':
                $builder->orderByRaw('CASE WHEN due_date < now() AND debt_amount > 0 THEN debt_amount END ' . $orderDirection . ' NULLS LAST')
                    ->orderBy('id', 'desc');
                break;
            default:
                $builder->orderBy($orderBy, $orderDirection)
                    ->orderBy('id', 'desc');
                break;

        }

        return $builder;
    }

    public function getOverdueDays(): null|int
    {
        if (!$this->isOverdue()) return null;

        return now()->diffInDays($this->due_date);
    }

    public function scopeOpen(Builder $builder): Builder
    {
        return $builder->where(function (Builder $q) {
            $q
                ->whereIn(Order::TABLE . '.status', [OrderStatus::New->value, OrderStatus::In_process->value])
                ->orWhere(function(Builder $q) {
                    $q->where(Order::TABLE . '.status', OrderStatus::Finished->value)
                        ->where(Order::TABLE . '.status_changed_at', '>', now()->addMinutes(config('orders.bs.do_not_change_finished_status_after') * -1));
                });
        });
    }

    public function scopeClosed(Builder $builder): Builder
    {
        return $builder
            ->where(Order::TABLE . '.status', OrderStatus::Finished->value)
            ->where(Order::TABLE . '.status_changed_at', '<=', now()->addMinutes(config('orders.bs.do_not_change_finished_status_after') * -1));
    }

    public function getPartsCost(): float
    {
        if(
            $this->inventories->isEmpty()
            || !$this->status->isFinished()
        ){
            return 0;
        }

        $date = $this->status_changed_at;

        $costs = [];

        foreach ($this->inventories as $inventory){
            /** @var $inventory TypeOfWorkInventory */
            $amountInOrder = $inventory->quantity;
            $transaction = $inventory->inventory->transactions->where('order_id', $this->id)->first();

            // первая транзакция на покупку товара, от это транзакции будем просчитывать, кол-во проданных товаров
            $firstPurchaseTransaction = $inventory
                ->inventory
                ->transactions()
                ->where('operation_type', OperationType::PURCHASE())
                ->oldest()
                ->first()
            ;

            if (!$firstPurchaseTransaction) continue;

            $purchaseData = [];
            $currentQuantity = 0;
            $tmp = $inventory
                ->inventory
                ->transactions()
                ->where('operation_type', OperationType::PURCHASE())
                ->orderBy('created_at')
                ->get()
                ->map(function(Transaction $i) use (&$purchaseData, &$currentQuantity) {
                    $currentQuantity += $i->quantity;
                    return [
                        'cost' => $i->price,
                        'quantity' => $currentQuantity
                    ];
                })
                ->toArray()
            ;

            // кол-во проданных товаров, до нашей продажи
            $amountSold = $inventory
                ->inventory
                ->transactions
                ->where('operation_type', OperationType::SOLD())
                ->where('created_at', '<', $transaction->created_at)
                ->where('created_at', '>', $firstPurchaseTransaction->created_at)
                ->where('is_reserve', false)
                ->sum('quantity');

            $tmp = array_values($tmp);

            $ftmp = [];
            foreach ($tmp as $k => $item){
                if($item['quantity'] > $amountSold){
                    // проверяем какое кол-во по какому косту считаем
                    if($item['quantity'] < ($amountSold + $amountInOrder)){
                        // кол-во, деталей которые мы считаем по этому косту
                        $amountThis = $item['quantity'] - $amountSold;
                        $ftmp[$k] = $item['cost'] * $amountThis;

                        // кол-во, которое считаем по следуещему косту
                        $amountLeft = $amountInOrder - $amountThis;
                        if(isset($tmp[$k+1])){
                            $ftmp[$k+1] = $tmp[$k+1]['cost'] * $amountLeft;
                        }
                    } else {
                        $ftmp[$k] = $item['cost'] * $amountInOrder;
                    }

                    break;
                }
            }
            $costs[] =array_sum($ftmp);
        }

        return round(array_sum($costs), 2);
    }
}
