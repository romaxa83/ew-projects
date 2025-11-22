<?php

namespace App\Models\Orders\Parts;

use App\Enums\Orders\Parts\ShippingMethod;
use App\Foundations\Models\BaseModel;
use Database\Factories\Orders\Parts\ShippingFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property float cost
 * @property ShippingMethod method
 * @property string|null terms
 * @property string|null track_number
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Order::order()
 * @property Order|BelongsTo order
 *
 * @see self::items()
 * @property Item[]|HasMany items
 *
 * @mixin Eloquent
 *
 * @method static ShippingFactory factory(...$parameters)
 */
class Shipping extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'parts_order_shippings';
    protected $table = self::TABLE;

    /**@var array<int, string>*/
    protected $fillable = [];

    protected $casts = [
        'method' => ShippingMethod::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'shipping_id');
    }
}
