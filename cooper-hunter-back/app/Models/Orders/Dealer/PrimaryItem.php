<?php

namespace App\Models\Orders\Dealer;

use App\Casts\PriceCast;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\ItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int item_id
 * @property int qty
 * @property float price
 *
 * @see PrimaryItem::item()
 * @property-read Item item
 *
 * @see Item::getAmountAttribute()
 * @property-read float amount
 *
 * @mixin Eloquent
 */
class PrimaryItem extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'dealer_order_primary_items';
    protected $table = self::TABLE;

    protected $fillable = [
        'price',
        'qty',
    ];

    protected $casts = [
        'price' => PriceCast::class,
        'total' => PriceCast::class,
    ];

    public function Item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
