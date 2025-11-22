<?php

namespace App\Models\Orders\Dealer;

use App\Casts\PriceCast;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\ItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property int product_id
 * @property int qty
 * @property float price
 * @property float discount         // скидка на ед. товара
 * @property float discount_total   // скидка на все кол-во товара
 * @property float total
 * @property string|null description
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Item::product()
 * @property-read Product product
 *
 * @see Item::order()
 * @property-read Order order
 *
 * @see Item::primary()
 * @property-read PrimaryItem primary
 *
 * @see Item::getAmountAttribute()
 * @property-read float amount
 *
 * @see Item::getAmountWithDiscountAttribute()
 * @property-read float amount_with_discount
 *
 * @method static ItemFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Item extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public const TABLE = 'dealer_order_items';
    protected $table = self::TABLE;

    protected $fillable = [
        'price',
        'qty',
        'discount',
        'discount_total'
    ];

    protected $casts = [
        'price' => PriceCast::class,
        'discount' => PriceCast::class,
        'discount_total' => PriceCast::class,
        'total' => PriceCast::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function primary(): HasOne
    {
        return $this->hasOne(PrimaryItem::class);
    }

    public function getAmountAttribute(): float
    {
        return pretty_price($this->qty * $this->price);
    }

    public function getAmountWithDiscountAttribute(): float
    {
        return pretty_price($this->amount - $this->discount_total);
    }
}
