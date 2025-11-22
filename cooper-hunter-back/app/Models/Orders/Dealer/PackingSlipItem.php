<?php

namespace App\Models\Orders\Dealer;

use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\PackingSlipItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int packing_slip_id
 * @property int product_id
 * @property int order_item_id
 * @property int qty
 * @property string|null description
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see PackingSlipItem::product()
 * @property-read Product product
 *
 * @see PackingSlipItem::orderItem()
 * @property-read Item orderItem
 *
 * @see PackingSlipItem::getAmountAttribute()
 * @property-read float amount
 *
 * @see PackingSlipItem::getTotalAttribute()
 * @property-read float total
 *
 * @method static PackingSlipItemFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class PackingSlipItem extends BaseModel
{
    use HasFactory;

    public const TABLE = 'dealer_order_packing_slip_items';
    protected $table = self::TABLE;

    protected $fillable = [
        'qty',
        'order_item_id',
    ];

    protected $casts = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packingSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getAmountAttribute(): float
    {
        return pretty_price($this->qty * $this->orderItem->price);
    }

    public function getTotalAttribute(): float
    {
        return pretty_price(($this->orderItem->price - $this->orderItem->discount) * $this->qty);
    }
}
