<?php

namespace App\Models\Orders\Parts;

use App\Foundations\Models\BaseModel;
use App\Models\Inventories\Inventory;
use Database\Factories\Orders\Parts\ItemFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property int inventory_id
 * @property int|null shipping_id
 * @property float qty
 * @property bool free_shipping
 * @property float|null price
 * @property float|null price_old
 * @property float|null delivery_cost
 * @property float|null discount // %
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Inventory::inventory()
 * @property Inventory|BelongsTo inventory
 *
 * @see Order::order()
 * @property Order|BelongsTo order
 *
 * @mixin Eloquent
 *
 * @method static ItemFactory factory(...$parameters)
 */
class Item extends BaseModel
{
    use Filterable;
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'parts_order_items';
    protected $table = self::TABLE;

    /**@var array<int, string>*/
    protected $fillable = [
        'shipping_id'
    ];

    protected $casts = [
        'free_shipping' => 'boolean',
        'price' => 'float',
        'price_old' => 'float',
        'delivery_cost' => 'float',
        'discount' => 'float',
    ];

    public function isOverload(): bool
    {
        return $this->inventory->weight > 150;
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id')->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getPrice(): float
    {
        if(is_null($this->delivery_cost)){
            return round(price_with_discount($this->price, $this->discount), 2);
        }

        $price = (
            price_with_discount($this->price, $this->discount)
            + price_with_discount($this->delivery_cost, $this->discount)
        ) ?? 0;

        return round($price, 2);
    }

    public function getPriceForTransaction(): float
    {
        return $this->getPrice();
    }

    public function getPriceOld(): float
    {
        return $this->price_old ?? 0;
    }

    public function total(): float
    {
        return $this->getPrice() * $this->qty;
    }

    public function subtotal(): float
    {
        $price = $this->price_old
            ? $this->getPriceOld()
            : $this->getPrice()
        ;

        return $price * $this->qty;
    }

    public function getPriceDiff(): float
    {
        if(!$this->price_old) return 0;

        return ($this->getPriceOld() - $this->getPrice()) * $this->qty;
    }

    public function getSaving(): float
    {
        return ($this->price_old - $this->getPrice()) * $this->qty;
    }

    public function getQty(): float|int
    {
        if($this->inventory->unit->accept_decimals) {
            return (float)$this->qty;
        }

        return (int)$this->qty;
    }
}
