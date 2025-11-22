<?php

namespace App\Models\BodyShop\Inventories;

use App\ModelFilters\BodyShop\Inventories\InventoryFilter;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWork as OrderTypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory as OrderTypeOfWorkInventory;
use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use App\Models\DiffableInterface;
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

/**
 * App\Models\BodyShop\Inventories
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $stock_number
 * @property int|null $category_id
 * @property float|null $price_retail
 * @property float $quantity
 * @property float $min_limit
 * @property int|null $supplier_id
 * @property string|null $notes
 * @property int $unit_id
 * @property bool $for_sale
 * @property float|null length
 * @property float|null width
 * @property float|null height
 * @property float|null weight
 * @property float|null min_limit_price
 *
 * @see Inventory::category()
 * @property Category $category
 *
 * @see Inventory::supplier()
 * @property Supplier $supplier
 *
 * @see Inventory::unit()
 * @property Unit $unit
 *
 * @see Inventory::transactions()
 * @property Transaction[] $transactions
 *
 * @mixin Eloquent
 */
class Inventory extends Model implements DiffableInterface
{
    use Filterable;
    use Diffable;
    use SoftDeletes;

    public const TABLE_NAME = 'bs_inventories';

    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'stock_number',
        'category_id',
        'price_retail',
        'quantity',
        'notes',
        'supplier_id',
        'unit_id',
        'min_limit',
        'for_sale',
        'length',
        'width',
        'height',
        'weight',
        'min_limit_price',
    ];

    protected $casts = [
        'length' => 'double',
        'width' => 'double',
        'height' => 'double',
        'weight' => 'double',
        'min_limit_price' => 'double',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(InventoryFilter::class);
    }

    public function getStatus(): string
    {
        return $this->quantity > 0 ? self::STATUS_IN_STOCK : self::STATUS_OUT_OF_STOCK;
    }

    public function category(): ?BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier(): ?BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function unit(): ?BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'inventory_id');
    }

    public function addTransaction(array $data, bool $fromReserve = false): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactions()->create($data);

        if (!$fromReserve && $transaction->isPurchase()) {
            $this->increaseQuantity($transaction->quantity);
        }

        if (!$fromReserve && $transaction->isSold()) {
            $this->decreaseQuantity($transaction->quantity);
        }

        return $transaction;
    }

    public function updateTransaction(int $orderId, float $newQuantity, float $oldQuantity): ?Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactions()
            ->where('order_id', $orderId)
            ->where('quantity', $oldQuantity)
            ->first();

        if ($transaction) {
            $transaction->quantity = $newQuantity;
            $transaction->save();

            if ($newQuantity < $oldQuantity) {
                $this->increaseQuantity($oldQuantity - $newQuantity);
            } else {
                $this->decreaseQuantity($newQuantity - $oldQuantity);
            }
        }

        return $transaction;
    }

    public function decreaseQuantity(float $quantity): void
    {
        $this->quantity -= $quantity;
        $this->save();
    }

    public function increaseQuantity(float $quantity): void
    {
        $this->quantity += $quantity;
        $this->save();
    }

    public function changeReservedPrice(Order $order): void
    {
        $this->transactions()
            ->where('order_id', $order->id)
            ->update(['price' => $this->price_retail]);
    }

    public function deleteReserve(Order $order, float $price, float $quantity): void
    {
        $transaction = $this->transactions()
            ->where('order_id', $order->id)
            ->where('price', $price)
            ->where('quantity', $quantity)
            ->first();

        if ($transaction) {
            $transaction->delete();
        }
    }

    public function markAsReserve(Order $order): void
    {
        $this->transactions()
            ->where('order_id', $order->id)
            ->update(['is_reserve' => true]);
    }

    public function orders()
    {
        return $this->hasManyThrough(
            OrderTypeOfWork::class,
            OrderTypeOfWorkInventory::class,
            'inventory_id',
            'id',
            'id',
            'type_of_work_id'
        )->leftJoin(Order::TABLE_NAME, Order::TABLE_NAME . '.id', '=', OrderTypeOfWork::TABLE_NAME . '.order_id');
    }

    public function openOrders()
    {
        return $this->orders()
            ->where(function (Builder $q) {
                $q
                    ->whereIn(Order::TABLE_NAME . '.status', [Order::STATUS_NEW, Order::STATUS_IN_PROCESS])
                    ->orWhere(function(Builder $q) {
                        $q->where(Order::TABLE_NAME . '.status', Order::STATUS_FINISHED)
                            ->where(Order::TABLE_NAME . '.status_changed_at', '>', now()->addMinutes(config('orders-bs.do_not_change_finished_status_after') * -1));
                    });
            });
    }

    public function deletedOrders()
    {
        return $this->orders()
            ->whereNotNull(Order::TABLE_NAME . '.deleted_at');
    }

    public function typesOfWork(): HasManyThrough
    {
        return $this->hasManyThrough(
            TypeOfWork::class,
            TypeOfWorkInventory::class,
            'inventory_id',
            'id',
            'id',
            'type_of_work_id'
        );
    }

    public function hasRelatedOpenOrders(): bool
    {
        return $this->openOrders()->exists();
    }

    public function hasRelatedDeletedOrders(): bool
    {
        return $this->deletedOrders()->exists();
    }

    public function hasRelatedTypesOfWork(): bool
    {
        return $this->typesOfWork()->exists();
    }

    public function hasRelatedEntities(): bool
    {
        return $this->hasRelatedTypesOfWork()
            || $this->hasRelatedOpenOrders()
            || $this->hasRelatedDeletedOrders();
    }

    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }
}
