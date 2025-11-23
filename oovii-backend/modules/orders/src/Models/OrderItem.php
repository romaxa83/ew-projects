<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lang;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Image\ImageHandler;

/**
 * \WezomCms\Orders\Models\OrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property float $quantity
 * @property float $price
 * @property float $purchase_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $discount
 * @property-read string $name
 * @property-read string $quantity_with_unit
 * @property-read string $unit
 * @property-read float $whole_price
 * @property-read float $whole_purchase_price
 * @property-read Order $order
 * @property-read Product $product
 * @method static Builder|OrderItem newModelQuery()
 * @method static Builder|OrderItem newQuery()
 * @method static Builder|OrderItem query()
 * @method static Builder|OrderItem whereCreatedAt($value)
 * @method static Builder|OrderItem whereId($value)
 * @method static Builder|OrderItem whereOrderId($value)
 * @method static Builder|OrderItem wherePrice($value)
 * @method static Builder|OrderItem whereProductId($value)
 * @method static Builder|OrderItem wherePurchasePrice($value)
 * @method static Builder|OrderItem whereQuantity($value)
 * @method static Builder|OrderItem whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderItem extends Model
{
    use HasFactory;

    public const TABLE = 'order_items';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['product_id', 'quantity', 'price', 'purchase_price'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getNameAttribute(): string
    {
        return $this->product ? $this->product->name : __('cms-orders::admin.orders.Product deleted');
    }

    public function getImageUrl(string $size = null, string $field = 'image'): ?string
    {
        return $this->product
            ? $this->product->getImageUrl($size, $field)
            : url(ImageHandler::noImage(50, 50));
    }

    public function getFrontUrl(): string
    {
        return $this->product ? $this->product->getFrontUrl() : '#';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getBonusesSum(): int
    {
        return $this->product->bonus * $this->quantity;
    }

    public function getDiscountAttribute(): int
    {
        return ($this->price - $this->purchase_price) * $this->quantity;
    }

    public function getWholePriceAttribute(): float
    {
        return $this->quantity * $this->price;
    }

    public function getWholePurchasePriceAttribute(): float
    {
        return $this->quantity * $this->purchase_price;
    }

    public function getQuantityWithUnitAttribute(): string
    {
        return $this->quantity . ' ' . $this->unit;
    }

    public function getUnitAttribute(): string
    {
        return $this->product ? $this->product->unit() : Lang::get('cms-orders::' . app('side') . '.pieces');
    }

    public function getWeight(): int
    {
        return $this->quantity * $this->product->getWeight();
    }

    public function getLength(): int
    {
        return $this->quantity * $this->product->getLength();
    }

    public function getWidth(): int
    {
        return $this->product->getWidth();
    }

    public function getHeight(): int
    {
        return $this->product->getHeight();
    }
}
