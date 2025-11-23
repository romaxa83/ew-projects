<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Casts\CartContentCast;

/**
 * WezomCms\Orders\Models\CartItem
 *
 * @property int $id
 * @property int $cart_id
 * @property string $unique_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property CartItemContent $content
 *
 * @see CartItem::cart()
 * @property-read Cart $cart
 *
 * @see CartItem::product()
 * @property-read Product $product
 *
 * @method static Builder|CartItem newModelQuery()
 * @method static Builder|CartItem newQuery()
 * @method static Builder|CartItem query()
 * @method static Builder|CartItem whereCartId($value)
 * @method static Builder|CartItem whereContent($value)
 * @method static Builder|CartItem whereCreatedAt($value)
 * @method static Builder|CartItem whereId($value)
 * @method static Builder|CartItem whereUniqueId($value)
 * @method static Builder|CartItem whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CartItem extends Model
{
    protected $casts = [
        'content' => CartContentCast::class,
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getProductIdAttribute(): int
    {
        return $this->content->getId();
    }

    public function getContentUniqueId(): string
    {
        return $this->content->getUniqueId();
    }

    public function getContentPrice(): float
    {
        return $this->content->getPrice();
    }

    public function getContentQuantity(): float
    {
        return $this->content->getQuantity();
    }

    public function getContentOptions(): array
    {
        return $this->content->getOptions();
    }
}
