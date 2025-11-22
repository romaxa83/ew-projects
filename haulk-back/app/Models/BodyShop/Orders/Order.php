<?php

namespace App\Models\BodyShop\Orders;

use App\Collections\Models\BodyShop\Orders\MediaCollection;
use App\ModelFilters\BodyShop\Orders\OrderFilter;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\DiffableInterface;
use App\Models\Files\BodyShop\OrderImage;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Scopes\CompanyScope;
use App\Traits\Diffable;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\BodyShop\Orders\Order
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $truck_id
 * @property int|null $trailer_id
 * @property float|null $discount
 * @property Carbon $implementation_date
 * @property int $mechanic_id
 * @property string $notes
 * @property string $status
 * @property string $order_number
 * @property float $tax_inventory
 * @property float $tax_labor
 * @property Carbon $due_date
 * @property Carbon $status_changed_at
 * @property string $status_before_deleting
 * @property bool|null $is_billed
 * @property Carbon |null $billed_at
 * @property bool|null $is_paid
 * @property Carbon|null $paid_at
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $debt_amount
 * @property float $profit
 * @property float $parts_cost
 *
 * @see Order::typesOfWork()
 * @property TypeOfWork[] $typesOfWork
 *
 * @see Order::truck()
 * @property Truck|null $truck
 *
 * @see Order::trailer()
 * @property Trailer|null $trailer
 *
 * @see Order::mechanic()
 * @property User $mechanic
 *
 * @see Order::vehicle()
 * @property Vehicle $vehicle
 *
 * @see Order::inventories()
 * @property TypeOfWorkInventory[] $inventories
 *
 * @see Order::comments()
 * @property OrderComment[] $comments
 *
 * @see Order::payments()
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
 */
class Order extends Model implements HasMedia, DiffableInterface
{
    use Filterable;
    use HasMediaTrait;
    use Diffable;
    use SoftDeletes;

    public const TABLE_NAME = 'bs_orders';

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_DELETED = 'deleted';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_IN_PROCESS,
        self::STATUS_FINISHED,
        self::STATUS_DELETED,
    ];

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_NOT_PAID = 'not_paid';
    public const PAYMENT_STATUS_BILLED = 'billed';
    public const PAYMENT_STATUS_NOT_BILLED = 'not_billed';
    public const PAYMENT_STATUS_OVERDUE = 'overdue';
    public const PAYMENT_STATUS_NOT_OVERDUE = 'not_overdue';
    public const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PAID,
        self::PAYMENT_STATUS_NOT_PAID,
        self::PAYMENT_STATUS_BILLED,
        self::PAYMENT_STATUS_NOT_BILLED,
        self::PAYMENT_STATUS_OVERDUE,
        self::PAYMENT_STATUS_NOT_OVERDUE,
    ];

    protected $table = self::TABLE_NAME;

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
        'parts_cost',
        'profit',
    ];

    protected $casts = [
        'implementation_date' => 'datetime',
        'due_date' => 'datetime',
        'status_changed_at' => 'datetime',
        'billed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(OrderFilter::class);
    }

    public function typesOfWork(): HasMany
    {
        return $this->hasMany(TypeOfWork::class, 'order_id');
    }

    public function truck(): ?BelongsTo
    {
        return $this->belongsTo(Truck::class, 'truck_id')->withTrashed();
    }

    public function trailer(): ?BelongsTo
    {
        return $this->belongsTo(Trailer::class, 'trailer_id')->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function vehicle(): BelongsTo
    {
        if ($this->truck_id) {
            return $this->truck();
        }

        return $this->trailer();
    }

    public function mechanic(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id')
            ->withoutGlobalScope(new CompanyScope())
            ->withTrashed();
    }

    public function getAmount(): float
    {
        $inventoryAmount = 0;
        $laborAmount = 0;

        foreach ($this->typesOfWork as $work) {
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

    public function getPartsCost(): float
    {
        if(
            $this->inventories->isEmpty()
            || !$this->isFinished()
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
                ->transactions
                ->where('operation_type', Transaction::OPERATION_TYPE_PURCHASE)
                ->sortBy('created_at')
                ->first()
            ;
            if (!$firstPurchaseTransaction) continue;

            $purchaseData = [];
            $currentQuantity = 0;
            $tmp = $inventory
                ->inventory
                ->transactions
                ->where('operation_type', Transaction::OPERATION_TYPE_PURCHASE)
                ->sortBy('created_at')
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
                ->where('operation_type', Transaction::OPERATION_TYPE_SOLD)
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
        $statuses = '{' . implode(',', self::STATUSES) . '}';
        return $builder
            ->addSelect(DB::raw('ARRAY_POSITION(\'' . $statuses . '\', status) as status_sort'))
            ->orderBy('status_sort', 'asc')
            ->orderBy('implementation_date', 'asc');
    }

    public function getRelationsForDiff(): array
    {
        return [
            'typesOfWork' => $this->typesOfWork,
            self::ATTACHMENT_COLLECTION_NAME => (
                new MediaCollection($this->getMedia(self::ATTACHMENT_COLLECTION_NAME))
            )->getAttributesForDiff(),
            'comments' => $this->comments,
            'payments' => $this->payments,
        ];
    }

    public function inventories(): HasManyThrough
    {
        return $this->hasManyThrough(
            TypeOfWorkInventory::class,
            TypeOfWork::class,
            'order_id',
            'type_of_work_id',
            'id', //
            'id'
        );
    }

    public function isPricesChanged(): bool
    {
        return $this->inventories()
            ->leftJoin(
                Inventory::TABLE_NAME,
                Inventory::TABLE_NAME . '.id', '=', TypeOfWorkInventory::TABLE_NAME . '.inventory_id')
            ->whereColumn(Inventory::TABLE_NAME . '.price_retail', '!=', TypeOfWorkInventory::TABLE_NAME . '.price')
            ->exists();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OrderComment::class);
    }

    public function changeStatus(string $newStatus): void
    {
        $this->status = $newStatus;
        $this->status_changed_at = now();
        $this->save();
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function isStatusCanBeChanged(): bool
    {
        if (
            $this->isFinished()
            && now()->timestamp - $this->status_changed_at->timestamp > config('orders-bs.do_not_change_finished_status_after') * 60
        ) {
            return false;
        }

        return true;
    }

    public function reassignMechanic(int $mechanicId): void
    {
        $this->mechanic_id = $mechanicId;
        $this->save();
    }

    public function isPaid(): bool
    {
        return $this->is_paid;
    }

    /**
     * @return bool|null
     */
    public function delete()
    {
        $this->status_before_deleting = $this->status;
        $this->changeStatus(self::STATUS_DELETED);

        return parent::delete();
    }

    /**
     * @return void
     */
    public function restoreOrder(): void
    {
        $this->changeStatus($this->status_before_deleting);
        $this->status_before_deleting = null;
        $this->save();

        $this->restore();
    }

    public function getPaymentsAmount(): float
    {
        $amount = 0;
        foreach ($this->payments as $payment) {
            $amount += $payment->amount;
        }

        return $amount;
    }

    public function getCurrentPaymentStatus(): ?string
    {
        if ($this->is_paid) {
            return self::PAYMENT_STATUS_PAID;
        }

        if ($this->is_billed) {
            return self::PAYMENT_STATUS_BILLED;
        }

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
            return;
        }

        if ($this->total_amount > $this->paid_amount && $this->is_paid) {
            $this->is_paid = false;
            $this->paid_at = null;
            $this->save();
            return;
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

    public function scopeOrderForReport(Builder $builder, string $orderBy, string $orderDirection): Builder
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

    public function getOverdueDays(): ?int
    {
        if (!$this->isOverdue()) {
            return null;
        }

        return now()->diffInDays($this->due_date);
    }

    public function scopeOpen(Builder $builder): Builder
    {
        return $builder->where(function (Builder $q) {
            $q
                ->whereIn(Order::TABLE_NAME . '.status', [Order::STATUS_NEW, Order::STATUS_IN_PROCESS])
                ->orWhere(function(Builder $q) {
                    $q->where(Order::TABLE_NAME . '.status', Order::STATUS_FINISHED)
                        ->where(Order::TABLE_NAME . '.status_changed_at', '>', now()->addMinutes(config('orders-bs.do_not_change_finished_status_after') * -1));
                });
        });
    }

    public function scopeClosed(Builder $builder): Builder
    {
        return $builder
            ->where(Order::TABLE_NAME . '.status', Order::STATUS_FINISHED)
            ->where(Order::TABLE_NAME . '.status_changed_at', '<=', now()->addMinutes(config('orders-bs.do_not_change_finished_status_after') * -1));
    }
}
