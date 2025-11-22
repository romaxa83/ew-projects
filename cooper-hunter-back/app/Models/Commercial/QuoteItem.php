<?php

namespace App\Models\Commercial;

use App\Casts\PriceCast;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property integer commercial_quote_id
 * @property integer|null product_id
 * @property string|null name
 * @property integer qty
 * @property float price
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Product|null product
 * @property-read CommercialQuote quote
 */
class QuoteItem extends BaseModel
{
    use HasFactory;

    public const TABLE = 'commercial_quote_items';
    protected $table = self::TABLE;

    protected $fillable = [
        'qty',
        'product_id',
    ];

    protected $casts = [
        'price' => PriceCast::class,
    ];

    protected $appends = [
        'total',
    ];

    public function product(): BelongsTo|Product
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function quote(): BelongsTo|CommercialQuote
    {
        return $this->belongsTo(CommercialQuote::class, 'commercial_quote_id', 'id');
    }

    public function getTotalAttribute(): float
    {
        return pretty_price($this->qty * $this->price);
    }

    public function getTitleAttribute(): string
    {
        return $this->name ?: $this->product->title;
    }
}

